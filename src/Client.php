<?php

declare(strict_types=1);

namespace Bannerify;

use Bannerify\Types\Modification;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Bannerify API Client
 * 
 * A simple, developer-friendly client for the Bannerify API.
 * Generate images and PDFs from templates at scale.
 */
class Client
{
    private HttpClient $httpClient;
    private string $apiKey;
    private string $baseUrl;

    /**
     * Create a new Bannerify client
     * 
     * @param string $apiKey Your Bannerify API key
     * @param array $options Optional configuration (baseUrl, timeout)
     */
    public function __construct(string $apiKey, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $options['baseUrl'] ?? 'https://api.bannerify.co/v1';
        
        $this->httpClient = new HttpClient([
            'base_uri' => $this->baseUrl,
            'timeout' => $options['timeout'] ?? 60.0,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'User-Agent' => 'bannerify-php/0.1.0',
            ],
        ]);
    }

    /**
     * Generate an image from a template
     * 
     * @param string $templateId Template ID (e.g., 'tpl_xxxxxxxxx')
     * @param array $options Options including modifications, format, thumbnail
     *   - modifications: array<Modification|array>
     *   - format: string
     *   - thumbnail: bool
     * @return array{result?: string|null, error?: array} Response with either result or error
     */
    public function createImage(string $templateId, array $options = []): array
    {
        try {
            $payload = [
                'apiKey' => $this->apiKey,
                'templateId' => $templateId,
                'modifications' => $options['modifications'] ?? [],
                'format' => $options['format'] ?? 'png',
                'thumbnail' => $options['thumbnail'] ?? false,
            ];

            $response = $this->httpClient->post('/templates/createImage', [
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                $contentType = $response->getHeaderLine('Content-Type');
                
                if (str_contains($contentType, 'image/svg')) {
                    return ['result' => $response->getBody()->getContents()];
                } else {
                    return ['result' => $response->getBody()->getContents()];
                }
            }

            return $this->buildError('HTTP_ERROR', "HTTP {$statusCode}");
        } catch (GuzzleException $e) {
            return $this->buildError('REQUEST_ERROR', $e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildError('EXCEPTION', $e->getMessage());
        }
    }

    /**
     * Generate a PDF from a template
     * 
     * @param string $templateId Template ID
     * @param array $options Options including modifications
     * @return array{result?: string|null, error?: array} Response with either result or error
     */
    public function createPdf(string $templateId, array $options = []): array
    {
        try {
            $payload = [
                'apiKey' => $this->apiKey,
                'templateId' => $templateId,
                'modifications' => $options['modifications'] ?? [],
            ];

            $response = $this->httpClient->post('/templates/createPdf', [
                'json' => $payload,
            ]);

            if ($response->getStatusCode() === 200) {
                return ['result' => $response->getBody()->getContents()];
            }

            return $this->buildError('HTTP_ERROR', 'HTTP ' . $response->getStatusCode());
        } catch (GuzzleException $e) {
            return $this->buildError('REQUEST_ERROR', $e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildError('EXCEPTION', $e->getMessage());
        }
    }

    /**
     * Create an image and store it on Bannerify's CDN
     * 
     * @param string $templateId Template ID
     * @param array $options Options including modifications, format, thumbnail
     * @return array{result?: string|null, error?: array} Response with URL or error
     */
    public function createStoredImage(string $templateId, array $options = []): array
    {
        try {
            $payload = [
                'apiKey' => $this->apiKey,
                'templateId' => $templateId,
                'modifications' => $options['modifications'] ?? [],
                'format' => $options['format'] ?? 'png',
                'thumbnail' => $options['thumbnail'] ?? false,
            ];

            $response = $this->httpClient->post('/templates/createStoredImage', [
                'json' => $payload,
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                return ['result' => $data['url'] ?? null];
            }

            return $this->buildError('HTTP_ERROR', 'HTTP ' . $response->getStatusCode());
        } catch (GuzzleException $e) {
            return $this->buildError('REQUEST_ERROR', $e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildError('EXCEPTION', $e->getMessage());
        }
    }

    /**
     * Generate a signed URL for on-demand image generation
     * 
     * @param string $templateId Template ID
     * @param array $options Options including modifications, format, thumbnail, nocache
     * @return string The signed URL
     */
    public function generateImageSignedUrl(string $templateId, array $options = []): string
    {
        $apiKeyHashed = hash('sha256', $this->apiKey);

        $params = [
            'apiKeyHashed' => $apiKeyHashed,
            'templateId' => $templateId,
        ];

        if (isset($options['format']) && $options['format'] === 'svg') {
            $params['format'] = 'svg';
        }

        if (isset($options['modifications'])) {
            $params['modifications'] = json_encode($options['modifications']);
        }

        if (isset($options['nocache']) && $options['nocache']) {
            $params['nocache'] = 'true';
        }

        if (isset($options['thumbnail']) && $options['thumbnail']) {
            $params['thumbnail'] = 'true';
        }

        ksort($params);

        $queryString = http_build_query($params);
        $sign = hash('sha256', $queryString . $apiKeyHashed);
        $params['sign'] = $sign;

        return $this->baseUrl . '/templates/signedurl?' . http_build_query($params);
    }

    /**
     * Build an error response
     * 
     * @param string $code Error code
     * @param string $message Error message
     * @return array Error response
     */
    private function buildError(string $code, string $message): array
    {
        return [
            'error' => [
                'code' => $code,
                'message' => $message,
                'docs' => 'https://bannerify.co/docs',
            ],
        ];
    }
}

