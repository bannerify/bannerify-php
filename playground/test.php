<?php

declare(strict_types=1);

/**
 * Playground test for Bannerify PHP SDK
 * 
 * Run with: php playground/test.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Bannerify\Client;
use Bannerify\Types\Modification;

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        putenv(trim($line));
    }
}

$apiKey = getenv('API_KEY');
$templateId = getenv('TEMPLATE_ID') ?: 'tpl_xxxxxxxxx';

if (!$apiKey) {
    echo "❌ API_KEY not found in .env file\n";
    exit(1);
}

echo "🎨 Bannerify PHP SDK Playground\n\n";

// Create output directory
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Initialize client
$client = new Client($apiKey);
echo "✅ Client initialized\n";

// Test 1: Create image with array modifications
echo "\n1️⃣ Test: Create image with array modifications\n";
$result = $client->createImage($templateId, [
    'modifications' => [
        ['name' => 'title', 'text' => 'PHP SDK Test'],
        ['name' => 'subtitle', 'text' => 'Using array modifications']
    ],
    'format' => 'png'
]);

if (isset($result['result'])) {
    $outputPath = $outputDir . '/test-array.png';
    file_put_contents($outputPath, $result['result']);
    echo "✅ Image created: {$outputPath}\n";
    echo "   Size: " . strlen($result['result']) . " bytes\n";
} else {
    echo "❌ Error: " . $result['error']['message'] . "\n";
}

// Test 2: Create image with Modification objects (type-safe!)
echo "\n2️⃣ Test: Create image with Modification objects (type-safe)\n";
$mods = [
    new Modification(name: 'title', text: 'Type-Safe PHP', color: '#8892BF'),
    new Modification(name: 'subtitle', text: 'Using typed objects', visible: true)
];

$result = $client->createImage($templateId, [
    'modifications' => $mods,
    'format' => 'png'
]);

if (isset($result['result'])) {
    $outputPath = $outputDir . '/test-typed.png';
    file_put_contents($outputPath, $result['result']);
    echo "✅ Image created: {$outputPath}\n";
    echo "   Size: " . strlen($result['result']) . " bytes\n";
} else {
    echo "❌ Error: " . $result['error']['message'] . "\n";
}

// Test 3: Create JPEG
echo "\n3️⃣ Test: Create JPEG image\n";
$result = $client->createImage($templateId, [
    'format' => 'jpeg',
    'modifications' => [
        new Modification(name: 'title', text: 'JPEG Output')
    ]
]);

if (isset($result['result'])) {
    $outputPath = $outputDir . '/test-jpeg.jpg';
    file_put_contents($outputPath, $result['result']);
    echo "✅ JPEG created: {$outputPath}\n";
    echo "   Size: " . strlen($result['result']) . " bytes\n";
} else {
    echo "❌ Error: " . $result['error']['message'] . "\n";
}

// Test 4: Generate signed URL
echo "\n4️⃣ Test: Generate signed URL\n";
$signedUrl = $client->generateImageSignedUrl($templateId, [
    'modifications' => [
        new Modification(name: 'title', text: 'Signed URL Test')
    ]
]);
echo "✅ Signed URL generated\n";
echo "   URL: " . substr($signedUrl, 0, 80) . "...\n";

// Test 5: Error handling
echo "\n5️⃣ Test: Error handling\n";
$result = $client->createImage('invalid_template_id');
if (isset($result['error'])) {
    echo "✅ Error handling works\n";
    echo "   Code: " . $result['error']['code'] . "\n";
    echo "   Message: " . $result['error']['message'] . "\n";
} else {
    echo "❌ Should have returned error\n";
}

echo "\n✨ All tests completed! Check the output/ directory for generated images.\n";

