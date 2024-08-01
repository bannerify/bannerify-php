<?php

/**
 * Code generated by Speakeasy (https://speakeasyapi.com). DO NOT EDIT.
 */

declare(strict_types=1);

namespace Bannerify\Bannerify;

class SDKConfiguration
{
    public ?\GuzzleHttp\ClientInterface $defaultClient = null;

    public ?\GuzzleHttp\ClientInterface $securityClient = null;

    public ?Models\Components\Security $security = null;
    /** @var pure-Closure(): string */
    public ?\Closure $securitySource = null;

    public string $serverUrl = '';

    public int $serverIndex = 0;

    public string $language = 'php';

    public string $openapiDocVersion = '0.0.0';

    public string $sdkVersion = '0.0.1';

    public string $genVersion = '2.379.3';

    public string $userAgent = 'speakeasy-sdk/php 0.0.1 2.379.3 0.0.0 bannerify/bannerify';

    public function getServerUrl(): string
    {

        if ($this->serverUrl !== '') {
            return $this->serverUrl;
        }

        return Bannerify::SERVERS[$this->serverIndex];
    }
    public function hasSecurity(): bool
    {
        return $this->security !== null || $this->securitySource !== null;
    }

    public function getSecurity(): ?Models\Components\Security
    {
        if ($this->securitySource !== null) {
            $security = new Models\Components\Security();
            $security->bearerAuth = $this->securitySource->call($this);

            return $security;
        } else {
            return $this->security;
        }
    }
}