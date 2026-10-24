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

/** A client whose HTTP calls get the $responses in turn and are recorded in $history. */
$mockedClient = function (array &$history, Response ...$responses) use ($vectors): Client {
    $stack = HandlerStack::create(new MockHandler($responses));
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

$sse = fn (array $events): Response => new Response(
    200,
    ['Content-Type' => 'text/event-stream'],
    implode('', array_map(fn (array $event) => "event: {$event['type']}\ndata: " . json_encode($event) . "\n\n", $events)),
);
$done = ['type' => 'done', 'url' => 'https://images.bannerify.co/files/a.mp4'];

$check('renderVideo streams the events of the render', function () use ($mockedClient, $assertSame, $sse, $done, $vectors) {
    $history = [];
    $client = $mockedClient($history, $sse([
        ['type' => 'queued', 'ahead' => 1],
        ['type' => 'progress', 'frame' => 30, 'totalFrames' => 60, 'progress' => 0.5],
        $done,
    ]));

    $events = iterator_to_array($client->renderVideo('tpl_x', [
        'modifications' => [new Modification(name: 'Title', text: 'Hi')],
    ]), false);

    $assertSame(['queued', 'progress', 'done'], array_column($events, 'type'));
    $request = $history[0]['request'];
    $assertSame('/v1/templates/renderVideo', $request->getUri()->getPath());
    $assertSame('stream=true', $request->getUri()->getQuery());
    $assertSame(
        ['apiKey' => $vectors['apiKey'], 'templateId' => 'tpl_x', 'modifications' => [['name' => 'Title', 'text' => 'Hi']]],
        json_decode((string) $request->getBody(), true),
    );
});

$check('renderVideo turns a refused request into one error event', function () use ($mockedClient, $assertSame) {
    $history = [];
    $error = [
        'code' => 'PRECONDITION_FAILED',
        'message' => 'Video rendering is not available yet.',
        'docs' => '',
        'requestId' => 'req_1',
    ];
    $client = $mockedClient($history, new Response(412, ['Content-Type' => 'application/json'], json_encode(['error' => $error])));

    $assertSame([['type' => 'error', 'error' => $error]], iterator_to_array($client->renderVideo('tpl_x'), false));
});

$check('renderVideo reads the same video again by its id when the stream drops', function () use ($mockedClient, $assertSame, $sse, $done, $vectors) {
    $history = [];
    $client = $mockedClient(
        $history,
        // The stream ends before 'done', like a lost connection.
        $sse([['type' => 'created', 'id' => 'vid_1'], ['type' => 'queued', 'ahead' => 0]]),
        $sse([['type' => 'progress', 'frame' => 60, 'totalFrames' => 60, 'progress' => 1], $done]),
    );

    $events = iterator_to_array($client->renderVideo('tpl_x'), false);

    $assertSame(['created', 'queued', 'progress', 'done'], array_column($events, 'type'));
    $assertSame(
        ['POST /v1/templates/renderVideo', 'GET /v1/videos/vid_1'],
        array_map(fn (array $call) => $call['request']->getMethod() . ' ' . $call['request']->getUri()->getPath(), $history),
    );
    parse_str($history[1]['request']->getUri()->getQuery(), $query);
    $assertSame(['apiKey' => $vectors['apiKey'], 'stream' => 'true'], $query);
});

$check('renderVideo does not send the render again when it fails before the video has an id', function () use ($mockedClient, $assertSame) {
    $history = [];
    $error = ['code' => 'INTERNAL_SERVER_ERROR', 'message' => 'Refused.', 'docs' => '', 'requestId' => 'r'];
    $client = $mockedClient($history, new Response(500, ['Content-Type' => 'application/json'], json_encode(['error' => $error])));

    $assertSame([['type' => 'error', 'error' => $error]], iterator_to_array($client->renderVideo('tpl_x'), false));
    $assertSame(1, count($history));
});

$check('getVideo answers the status and the URL of a video', function () use ($mockedClient, $assertSame, $done, $vectors) {
    $history = [];
    $video = ['id' => 'vid_1', 'status' => 'done', 'url' => $done['url']];
    $client = $mockedClient($history, new Response(200, ['Content-Type' => 'application/json'], json_encode($video)));

    $assertSame(['result' => $video], $client->getVideo('vid_1'));
    $assertSame('/v1/videos/vid_1', $history[0]['request']->getUri()->getPath());
    $assertSame('apiKey=' . urlencode($vectors['apiKey']), $history[0]['request']->getUri()->getQuery());
});

$check('createVideo answers the id of the video at once', function () use ($mockedClient, $assertSame) {
    $history = [];
    $created = ['id' => 'vid_1', 'status' => 'queued'];
    $client = $mockedClient($history, new Response(202, ['Content-Type' => 'application/json'], json_encode($created)));

    $assertSame(['result' => $created], $client->createVideo('tpl_x', [['name' => 'title', 'text' => 'Hi']]));
    $assertSame(1, count($history));
    $request = $history[0]['request'];
    $assertSame('POST', $request->getMethod());
    $assertSame('/v1/templates/renderVideo', $request->getUri()->getPath());
    $assertSame('wait=false', $request->getUri()->getQuery());
    $body = json_decode((string) $request->getBody(), true);
    $assertSame('tpl_x', $body['templateId']);
    $assertSame([['name' => 'title', 'text' => 'Hi']], $body['modifications']);
});

$check('renderAndWaitVideo answers the URL of the video, or the error', function () use ($mockedClient, $assertSame, $sse, $done) {
    $history = [];
    $waited = $mockedClient($history, $sse([['type' => 'queued', 'ahead' => 0], $done]));
    $assertSame(['result' => $done['url']], $waited->renderAndWaitVideo('tpl_x'));

    $error = ['code' => 'INTERNAL_SERVER_ERROR', 'message' => 'Could not download a clip.', 'requestId' => 'req_2'];
    $failed = $mockedClient($history, $sse([['type' => 'error', 'error' => $error]]));
    $assertSame(['error' => $error], $failed->renderAndWaitVideo('tpl_x'));
});

echo $failures === 0 ? "\nAll tests passed\n" : "\n$failures test(s) failed\n";
exit($failures === 0 ? 0 : 1);
