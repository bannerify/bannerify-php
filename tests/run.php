<?php

declare(strict_types=1);

// Run with: composer test

require __DIR__ . '/../vendor/autoload.php';

use Bannerify\Client;
use Bannerify\Types\Modification;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

// The vectors come from the JavaScript URLSearchParams, which the server uses to
// verify a signed URL.
$vectors = json_decode(
    file_get_contents(__DIR__ . '/../testdata/signed-url-vectors.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);

$failures = 0;

$check = function (string $name, callable $test) use (&$failures): void {
    try {
        $test();
        echo "✓ $name\n";
    } catch (Throwable $e) {
        $failures++;
        echo "✗ $name\n    " . str_replace("\n", "\n    ", $e->getMessage()) . "\n";
    }
};

$assertSame = function (mixed $expected, mixed $actual, string $label = '') {
    if ($expected !== $actual) {
        throw new RuntimeException(
            ($label !== '' ? "$label\n" : '')
            . 'expected: ' . var_export($expected, true) . "\nactual:   " . var_export($actual, true),
        );
    }
};

$queryPair = function (string $url, string $key): ?string {
    foreach (explode('&', explode('?', $url, 2)[1]) as $pair) {
        if (str_starts_with($pair, "$key=")) {
            return $pair;
        }
    }

    return null;
};

/** A client whose HTTP calls go to a mock and are recorded in $history. */
$mockedClient = function (array &$history, Response $response) use ($vectors): Client {
    $stack = HandlerStack::create(new MockHandler([$response]));
    $stack->push(Middleware::history($history));

    $client = new Client($vectors['apiKey'], ['baseUrl' => $vectors['baseUrl']]);
    (new ReflectionProperty(Client::class, 'httpClient'))->setValue(
        $client,
        new HttpClient(['handler' => $stack, 'base_uri' => $vectors['baseUrl'] . '/']),
    );

    return $client;
};

$signer = new Client($vectors['apiKey'], ['baseUrl' => $vectors['baseUrl']]);

foreach ($vectors['encoded'] as $vector) {
    $check('encodes ' . json_encode($vector['input']) . ' like URLSearchParams', function () use ($signer, $vector, $queryPair, $assertSame) {
        $url = $signer->generateImageSignedUrl($vector['input']);

        $assertSame('templateId=' . $vector['encoded'], $queryPair($url, 'templateId'));
    });
}

foreach ($vectors['golden'] as $golden) {
    $check('builds the golden URL: ' . $golden['name'], function () use ($signer, $golden, $assertSame) {
        $options = ['format' => $golden['format']];
        if ($golden['modifications'] !== null) {
            $options['modifications'] = $golden['modifications'];
        }

        $assertSame($golden['url'], $signer->generateImageSignedUrl($golden['templateId'], $options));
    });
}

$check('signs the sorted query', function () use ($signer, $vectors, $assertSame) {
    $url = $signer->generateImageSignedUrl('tpl_x', [
        'format' => 'jpeg',
        'modifications' => [['name' => 'title', 'text' => 'A*B~C']],
    ]);

    $pairs = explode('&', explode('?', $url, 2)[1]);
    $sign = array_pop($pairs);
    $sorted = $pairs;
    sort($sorted);

    $assertSame($sorted, $pairs, 'parameters are not sorted by name');
    $assertSame('sign=' . hash_hmac('sha256', implode('&', $pairs), $vectors['apiKey']), $sign);
});

$check('rejects an unsupported format for a signed URL', function () use ($signer) {
    try {
        $signer->generateImageSignedUrl('tpl_x', ['format' => 'gif']);
    } catch (InvalidArgumentException $e) {
        return;
    }

    throw new RuntimeException('expected an InvalidArgumentException');
});

$check('createImage sends no thumbnail field', function () use ($mockedClient, $assertSame) {
    $history = [];
    $client = $mockedClient($history, new Response(200, [], 'image'));

    $result = $client->createImage('tpl_x', ['thumbnail' => true]);

    $assertSame(['result' => 'image'], $result);
    $body = json_decode((string) $history[0]['request']->getBody(), true);
    $keys = array_keys($body);
    sort($keys);
    $assertSame(['apiKey', 'format', 'modifications', 'templateId'], $keys);
});

$check('createStoredImage sends no thumbnail field', function () use ($mockedClient, $assertSame) {
    $history = [];
    $client = $mockedClient($history, new Response(200, [], '{"url":"https://images.bannerify.co/files/a.png"}'));

    $result = $client->createStoredImage('tpl_x', ['thumbnail' => true]);

    $assertSame(['result' => 'https://images.bannerify.co/files/a.png'], $result);
    $body = json_decode((string) $history[0]['request']->getBody(), true);
    $keys = array_keys($body);
    sort($keys);
    $assertSame(['apiKey', 'format', 'modifications', 'templateId'], $keys);
});

$check('Modification objects leave out null fields in a request', function () use ($mockedClient, $assertSame) {
    $history = [];
    $client = $mockedClient($history, new Response(200, [], 'image'));

    $client->createImage('tpl_x', [
        'modifications' => [
            new Modification(name: 'title', text: 'Hello'),
            new Modification(name: 'photo', src: 'https://example.com/a.png', visible: false),
        ],
    ]);

    $body = json_decode((string) $history[0]['request']->getBody(), true);
    $assertSame(
        [
            ['name' => 'title', 'text' => 'Hello'],
            ['name' => 'photo', 'src' => 'https://example.com/a.png', 'visible' => false],
        ],
        $body['modifications'],
    );
});

$check('Modification objects leave out null fields in a signed URL', function () use ($signer, $queryPair, $assertSame) {
    $url = $signer->generateImageSignedUrl('tpl_x', [
        'modifications' => [new Modification(name: 'title', text: 'Hello')],
    ]);

    parse_str((string) $queryPair($url, 'modifications'), $query);
    $assertSame('[{"name":"title","text":"Hello"}]', $query['modifications']);
});

$check('returns the API error object', function () use ($mockedClient, $assertSame) {
    $history = [];
    $error = [
        'code' => 'BAD_REQUEST',
        'message' => 'Invalid api key',
        'docs' => 'https://bannerify.co/docs/api-reference/errors/code/BAD_REQUEST',
        'requestId' => 'req:abc123',
    ];
    $client = $mockedClient($history, new Response(400, ['Content-Type' => 'application/json'], json_encode(['error' => $error])));

    $assertSame(['error' => $error], $client->createImage('tpl_x'));
});

echo $failures === 0 ? "\nAll tests passed\n" : "\n$failures test(s) failed\n";
exit($failures === 0 ? 0 : 1);
