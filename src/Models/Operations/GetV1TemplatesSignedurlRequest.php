<?php

/**
 * Code generated by Speakeasy (https://speakeasyapi.com). DO NOT EDIT.
 */

declare(strict_types=1);

namespace Bannerify\Bannerify\Models\Operations;

use Bannerify\Bannerify\Utils\SpeakeasyMetadata;
class GetV1TemplatesSignedurlRequest
{
    #[SpeakeasyMetadata('queryParam:style=form,explode=true,name=format')]
    public ?QueryParamFormat $format = null;

    #[SpeakeasyMetadata('queryParam:style=form,explode=true,name=nocache')]
    public ?string $nocache = null;

    #[SpeakeasyMetadata('queryParam:style=form,explode=true,name=_debug')]
    public ?string $debug = null;

    #[SpeakeasyMetadata('queryParam:style=form,explode=true,name=templateId')]
    public string $templateId;

    #[SpeakeasyMetadata('queryParam:style=form,explode=true,name=apiKeyMd5')]
    public string $apiKeyMd5;

    #[SpeakeasyMetadata('queryParam:style=form,explode=true,name=sign')]
    public string $sign;

    #[SpeakeasyMetadata('queryParam:style=form,explode=true,name=modifications')]
    public ?string $modifications = null;

    public function __construct()
    {
        $this->format = null;
        $this->nocache = null;
        $this->debug = null;
        $this->templateId = '';
        $this->apiKeyMd5 = '';
        $this->sign = '';
        $this->modifications = null;
    }
}