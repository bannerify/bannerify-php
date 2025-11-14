<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Bannerify\Client;

$apiKey = getenv('BANNERIFY_API_KEY');
$templateId = getenv('BANNERIFY_TEMPLATE_ID');

if (!$apiKey || !$templateId) {
    echo "⚠️  Skipping integration tests - BANNERIFY_API_KEY or BANNERIFY_TEMPLATE_ID not set\n";
    exit(0);
}

echo "Running Bannerify PHP SDK integration tests...\n\n";

$failed = false;

// Test 1: Create client
echo "Test 1: Creating client... ";
try {
    $client = new Client($apiKey);
    echo "✓ PASSED\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Generate signed URL
echo "Test 2: Generating signed URL... ";
try {
    $signedUrl = $client->generateImageSignedUrl($templateId, [
        'modifications' => [
            ['name' => 'title', 'text' => 'Integration Test']
        ]
    ]);
    
    if (str_starts_with($signedUrl, 'https://api.bannerify.co/v1/templates/signedurl')) {
        echo "✓ PASSED\n";
        echo "   URL: " . substr($signedUrl, 0, 80) . "...\n";
    } else {
        echo "✗ FAILED: Invalid URL format\n";
        $failed = true;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed = true;
}

// Test 3: Create image (SVG)
echo "Test 3: Creating image (SVG format)... ";
try {
    $result = $client->createImage($templateId, [
        'format' => 'svg',
        'modifications' => [
            ['name' => 'title', 'text' => 'PHP SDK Test']
        ]
    ]);
    
    if (isset($result['result'])) {
        $svg = $result['result'];
        if (str_contains($svg, '<svg')) {
            echo "✓ PASSED\n";
            echo "   SVG size: " . strlen($svg) . " bytes\n";
        } else {
            echo "✗ FAILED: Response doesn't contain SVG\n";
            $failed = true;
        }
    } else {
        echo "✗ FAILED: " . ($result['error']['message'] ?? 'Unknown error') . "\n";
        $failed = true;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed = true;
}

// Test 4: Create image (PNG)
echo "Test 4: Creating image (PNG format)... ";
try {
    $result = $client->createImage($templateId, [
        'modifications' => [
            ['name' => 'title', 'text' => 'PHP SDK Test PNG']
        ]
    ]);
    
    if (isset($result['result'])) {
        $png = $result['result'];
        if (strlen($png) > 0) {
            echo "✓ PASSED\n";
            echo "   PNG size: " . strlen($png) . " bytes\n";
        } else {
            echo "✗ FAILED: Empty response\n";
            $failed = true;
        }
    } else {
        echo "✗ FAILED: " . ($result['error']['message'] ?? 'Unknown error') . "\n";
        $failed = true;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed = true;
}

// Test 5: Error handling
echo "Test 5: Testing error handling... ";
try {
    $result = $client->createImage('invalid_template_id');
    
    if (isset($result['error'])) {
        echo "✓ PASSED\n";
        echo "   Error code: " . $result['error']['code'] . "\n";
    } else {
        echo "✗ FAILED: Should have returned error\n";
        $failed = true;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed = true;
}

if ($failed) {
    echo "\n❌ Some tests failed\n";
    exit(1);
}

echo "\n✅ All integration tests passed!\n";
exit(0);
