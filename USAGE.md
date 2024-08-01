<!-- Start SDK Example Usage [usage] -->
```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Bannerify\Bannerify;
use Bannerify\Bannerify\Models\Components;
use Bannerify\Bannerify\Models\Operations;

$security = new Components\Security();
$security->bearerAuth = 'BANNERIFY_API_KEY';

$sdk = Bannerify\Bannerify::builder()
    ->setSecurity($security)
    ->build();

try {
    $request = new Operations\PostV1TemplatesCreateImageRequestBody();
    $request->format = Operations\Format::Svg;
    $request->debug = '<value>';
    $request->apiKey = 'key_xxxxxxxxx';
    $request->templateId = 'tpl_xxxxxxxxx';
    $request->modifications = [new Components\Modification()];

    $response = $sdk->postV1TemplatesCreateImage($request);

    if ($response->bytes !== null) {
        // handle response
    }
} catch (Throwable $e) {
    // handle exception
}

```
<!-- End SDK Example Usage [usage] -->