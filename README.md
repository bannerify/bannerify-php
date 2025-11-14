# Bannerify PHP SDK

Official PHP SDK for [Bannerify](https://bannerify.co) - Generate images and PDFs at scale via API.

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://php.net)

## Installation

Install via Composer:

```bash
composer require bannerify/bannerify
```

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use Bannerify\Client;

// Create client with your API key
$client = new Client('your-api-key');

// Generate an image
$result = $client->createImage('tpl_xxxxxxxxx', [
    'modifications' => [
        ['name' => 'title', 'text' => 'Hello World'],
        ['name' => 'subtitle', 'text' => 'From PHP SDK']
    ]
]);

if (isset($result['result'])) {
    file_put_contents('output.png', $result['result']);
    echo "Image created successfully!";
} else {
    echo "Error: " . $result['error']['message'];
}
```

## Features

- 🚀 Simple, intuitive API
- 🔒 Type-safe with PHPStan support
- ⚡ Built on Guzzle for reliability
- 🎯 Result/Error pattern for explicit error handling
- 📝 Comprehensive documentation
- ✅ Well-tested

## Usage

### Creating Images

```php
// Generate PNG
$result = $client->createImage('tpl_xxxxxxxxx', [
    'modifications' => [
        ['name' => 'title', 'text' => 'My Title'],
        ['name' => 'image', 'src' => 'https://example.com/image.jpg']
    ]
]);

// Generate SVG
$result = $client->createImage('tpl_xxxxxxxxx', [
    'format' => 'svg',
    'modifications' => [
        ['name' => 'title', 'text' => 'My Title']
    ]
]);

// Generate thumbnail
$result = $client->createImage('tpl_xxxxxxxxx', [
    'thumbnail' => true
]);
```

### Creating PDFs

```php
$result = $client->createPdf('tpl_xxxxxxxxx', [
    'modifications' => [
        ['name' => 'title', 'text' => 'Invoice #123']
    ]
]);

if (isset($result['result'])) {
    file_put_contents('invoice.pdf', $result['result']);
}
```

### Generating Signed URLs

```php
$signedUrl = $client->generateImageSignedUrl('tpl_xxxxxxxxx', [
    'modifications' => [
        ['name' => 'title', 'text' => 'Dynamic Title']
    ],
    'format' => 'png'
]);

// Use in HTML
echo "<img src='{$signedUrl}' alt='Generated Image' />";
```

### Error Handling

```php
$result = $client->createImage('tpl_xxxxxxxxx');

if (isset($result['error'])) {
    $error = $result['error'];
    echo "Error Code: " . $error['code'] . "\n";
    echo "Message: " . $error['message'] . "\n";
    echo "Docs: " . $error['docs'] . "\n";
} else {
    file_put_contents('output.png', $result['result']);
}
```

## API Reference

### Constructor

```php
new Client(string $apiKey, array $options = [])
```

**Options:**
- `baseUrl` (string): API base URL
- `timeout` (float): Request timeout in seconds

### createImage

```php
createImage(string $templateId, array $options = []): array
```

**Options:**
- `modifications` (array): Array of modifications
- `format` (string): 'png' or 'svg'
- `thumbnail` (bool): Generate thumbnail

**Returns:** Array with `result` (string) or `error` (array)

### createPdf

```php
createPdf(string $templateId, array $options = []): array
```

### createStoredImage

```php
createStoredImage(string $templateId, array $options = []): array
```

### generateImageSignedUrl

```php
generateImageSignedUrl(string $templateId, array $options = []): string
```

## Examples

### Generate Multiple Images

```php
$products = [
    ['name' => 'Product A', 'price' => '$29.99'],
    ['name' => 'Product B', 'price' => '$39.99'],
];

foreach ($products as $i => $product) {
    $result = $client->createImage('tpl_product_banner', [
        'modifications' => [
            ['name' => 'product_name', 'text' => $product['name']],
            ['name' => 'price', 'text' => $product['price']]
        ]
    ]);
    
    if (isset($result['result'])) {
        file_put_contents("banner_{$i}.png", $result['result']);
    }
}
```

### Email Campaigns

```php
foreach ($recipients as $recipient) {
    $signedUrl = $client->generateImageSignedUrl('tpl_email_header', [
        'modifications' => [
            ['name' => 'name', 'text' => "Hi, {$recipient['name']}!"]
        ]
    ]);
    // Use $signedUrl in email HTML
}
```

## Documentation

Full documentation available at [https://bannerify.co/docs/sdk/php/overview](https://bannerify.co/docs/sdk/php/overview)

## Support

- Documentation: https://bannerify.co/docs
- Issues: https://github.com/bannerify/bannerify-php/issues
- Email: support@bannerify.co

## License

MIT License - see LICENSE file for details
