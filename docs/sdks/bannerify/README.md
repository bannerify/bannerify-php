# Bannerify SDK


## Overview

### Available Operations

* [postV1TemplatesCreateImage](#postv1templatescreateimage)
* [getV1TemplatesSignedurl](#getv1templatessignedurl)
* [getV1Info](#getv1info) - Get project info

## postV1TemplatesCreateImage

### Example Usage

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use \Bannerify\Bannerify;
use \Bannerify\Bannerify\Models\Components;
use \Bannerify\Bannerify\Models\Operations;

$security = new Components\Security();
$security->bearerAuth = 'BANNERIFY_API_KEY';

$sdk = Bannerify\Bannerify::builder()->setSecurity($security)->build();

try {
        $request = new Operations\PostV1TemplatesCreateImageRequestBody();
    $request->format = Operations\Format::Svg;
    $request->debug = '<value>';
    $request->apiKey = 'key_xxxxxxxxx';
    $request->templateId = 'tpl_xxxxxxxxx';
    $request->modifications = [
        new Components\Modification(),
    ];;

    $response = $sdk->postV1TemplatesCreateImage($request);

    if ($response->bytes !== null) {
        // handle response
    }
} catch (Throwable $e) {
    // handle exception
}
```

### Parameters

| Parameter                                                                                                                                        | Type                                                                                                                                             | Required                                                                                                                                         | Description                                                                                                                                      |
| ------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| `$request`                                                                                                                                       | [\Bannerify\Bannerify\Models\Operations\PostV1TemplatesCreateImageRequestBody](../../Models/Operations/PostV1TemplatesCreateImageRequestBody.md) | :heavy_check_mark:                                                                                                                               | The request object to use for the request.                                                                                                       |


### Response

**[?\Bannerify\Bannerify\Models\Operations\PostV1TemplatesCreateImageResponse](../../Models/Operations/PostV1TemplatesCreateImageResponse.md)**


## getV1TemplatesSignedurl

### Example Usage

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use \Bannerify\Bannerify;
use \Bannerify\Bannerify\Models\Components;
use \Bannerify\Bannerify\Models\Operations;

$security = new Components\Security();
$security->bearerAuth = 'BANNERIFY_API_KEY';

$sdk = Bannerify\Bannerify::builder()->setSecurity($security)->build();

try {
        $request = new Operations\GetV1TemplatesSignedurlRequest();
    $request->format = Operations\QueryParamFormat::Svg;
    $request->nocache = 'true';
    $request->debug = '<value>';
    $request->templateId = 'tpl_xxxxxxxxx';
    $request->apiKeyMd5 = '<value>';
    $request->sign = '<value>';
    $request->modifications = '<value>';;

    $response = $sdk->getV1TemplatesSignedurl($request);

    if ($response->body !== null) {
        // handle response
    }
} catch (Throwable $e) {
    // handle exception
}
```

### Parameters

| Parameter                                                                                                                          | Type                                                                                                                               | Required                                                                                                                           | Description                                                                                                                        |
| ---------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `$request`                                                                                                                         | [\Bannerify\Bannerify\Models\Operations\GetV1TemplatesSignedurlRequest](../../Models/Operations/GetV1TemplatesSignedurlRequest.md) | :heavy_check_mark:                                                                                                                 | The request object to use for the request.                                                                                         |


### Response

**[?\Bannerify\Bannerify\Models\Operations\GetV1TemplatesSignedurlResponse](../../Models/Operations/GetV1TemplatesSignedurlResponse.md)**


## getV1Info

Get project info

### Example Usage

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use \Bannerify\Bannerify;
use \Bannerify\Bannerify\Models\Components;
use \Bannerify\Bannerify\Models\Operations;

$security = new Components\Security();
$security->bearerAuth = 'BANNERIFY_API_KEY';

$sdk = Bannerify\Bannerify::builder()->setSecurity($security)->build();

try {
    

    $response = $sdk->getV1Info('key_xxxxxxxxx');

    if ($response->object !== null) {
        // handle response
    }
} catch (Throwable $e) {
    // handle exception
}
```

### Parameters

| Parameter          | Type               | Required           | Description        | Example            |
| ------------------ | ------------------ | ------------------ | ------------------ | ------------------ |
| `apiKey`           | *string*           | :heavy_check_mark: | N/A                | key_xxxxxxxxx      |


### Response

**[?\Bannerify\Bannerify\Models\Operations\GetV1InfoResponse](../../Models/Operations/GetV1InfoResponse.md)**

