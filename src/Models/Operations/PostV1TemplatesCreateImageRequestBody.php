<?php

/**
 * Code generated by Speakeasy (https://speakeasyapi.com). DO NOT EDIT.
 */

declare(strict_types=1);

namespace Bannerify\Bannerify\Models\Operations;


class PostV1TemplatesCreateImageRequestBody
{
    #[\JMS\Serializer\Annotation\SerializedName('format')]
    #[\JMS\Serializer\Annotation\Type('enum<Bannerify\Bannerify\Models\Operations\Format>')]
    #[\JMS\Serializer\Annotation\SkipWhenEmpty]
    public ?Format $format = null;

    /**
     * Only for debug purpose, it draws bounding box for each layer
     *
     * @var ?string $debug
     */
    #[\JMS\Serializer\Annotation\SerializedName('_debug')]
    #[\JMS\Serializer\Annotation\Type('string')]
    #[\JMS\Serializer\Annotation\SkipWhenEmpty]
    public ?string $debug = null;

    /**
     * The api key to use for this request
     *
     * @var string $apiKey
     */
    #[\JMS\Serializer\Annotation\SerializedName('apiKey')]
    #[\JMS\Serializer\Annotation\Type('string')]
    public string $apiKey;

    /**
     * Your template id
     *
     * @var string $templateId
     */
    #[\JMS\Serializer\Annotation\SerializedName('templateId')]
    #[\JMS\Serializer\Annotation\Type('string')]
    public string $templateId;

    /**
     * $modifications
     *
     * @var ?array<\Bannerify\Bannerify\Models\Components\Modification> $modifications
     */
    #[\JMS\Serializer\Annotation\SerializedName('modifications')]
    #[\JMS\Serializer\Annotation\Type('array<Bannerify\Bannerify\Models\Components\Modification>')]
    #[\JMS\Serializer\Annotation\SkipWhenEmpty]
    public ?array $modifications = null;

    public function __construct()
    {
        $this->format = null;
        $this->debug = null;
        $this->apiKey = '';
        $this->templateId = '';
        $this->modifications = null;
    }
}