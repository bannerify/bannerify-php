<?php

declare(strict_types=1);

namespace Bannerify;

use Bannerify\Types\Modification;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * Bannerify API Client
 * 
 * A simple, developer-friendly client for the Bannerify API.
 * Generate images and PDFs from templates at scale.
 */
class Client
{
    private const SUPPORTED_FORMATS = ['png', 'jpeg', 'webp'];

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
        $this->baseUrl = rtrim($options['baseUrl'] ?? 'https://api.bannerify.co/v1', '/');

        $this->httpClient = new HttpClient([
            // Trailing slash required so relative paths resolve under /v1/
            'base_uri' => $this->baseUrl . '/',
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
     *   - format: string ('png', 'jpeg', 'webp')
     *   - thumbnail: bool
     * @return array{result?: string|null, error?: array} Response with either result or error
     */
    public function createImage(string $templateId, array $options = []): array
    {
        try {
            $format = $this->normalizeFormat($options['format'] ?? null);
            if ($format === null) {
                return $this->buildError('INVALID_FORMAT', 'Unsupported format. Use png, jpeg, or webp.');
            }

            $payload = [
                'apiKey' => $this->apiKey,
                'templateId' => $templateId,
                'modifications' => $options['modifications'] ?? [],
                'format' => $format,
                'thumbnail' => $options['thumbnail'] ?? false,
            ];

            $response = $this->httpClient->post('templates/createImage', [
                'json' => $payload,
                'http_errors' => false,
            ]);

            return $this->parseResponse($response);
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

            $response = $this->httpClient->post('templates/createPdf', [
                'json' => $payload,
                'http_errors' => false,
            ]);

            return $this->parseResponse($response);
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
     * @param array $options Options including modifications, format ('png','jpeg','webp'), thumbnail
     * @return array{result?: string|null, error?: array} Response with URL or error
     */
    public function createStoredImage(string $templateId, array $options = []): array
    {
        try {
            $format = $this->normalizeFormat($options['format'] ?? null);
            if ($format === null) {
                return $this->buildError('INVALID_FORMAT', 'Unsupported format. Use png, jpeg, or webp.');
            }

            $payload = [
                'apiKey' => $this->apiKey,
                'templateId' => $templateId,
                'modifications' => $options['modifications'] ?? [],
                'format' => $format,
                'thumbnail' => $options['thumbnail'] ?? false,
            ];

            $response = $this->httpClient->post('templates/createStoredImage', [
                'json' => $payload,
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                return ['result' => $data['url'] ?? null];
            }

            return $this->parseErrorResponse($response);
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
     * @param array $options Options including modifications, format ('png','jpeg','webp'), thumbnail, nocache
     * @return string The signed URL
     */
    public function generateImageSignedUrl(string $templateId, array $options = []): string
    {
        $apiKeyHashed = hash('sha256', $this->apiKey);

        $params = [
            'apiKeyHashed' => $apiKeyHashed,
            'templateId' => $templateId,
        ];

        if (isset($options['format'])) {
            $format = $this->normalizeFormat($options['format']);
            if ($format === null) {
                throw new InvalidArgumentException('format must be one of: png, jpeg, webp');
            }
            $params['format'] = $format;
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
     * @return array{result?: string|null, error?: array}
     */
    private function parseResponse(\Psr\Http\Message\ResponseInterface $response): array
    {
        if ($response->getStatusCode() === 200) {
            return ['result' => $response->getBody()->getContents()];
        }

        return $this->parseErrorResponse($response);
    }

    /**
     * @return array{error: array}
     */
    private function parseErrorResponse(\Psr\Http\Message\ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();
        $contentType = $response->getHeaderLine('Content-Type');

        if ($body !== '' && str_contains($contentType, 'application/json')) {
            $data = json_decode($body, true);
            if (is_array($data) && isset($data['error']) && is_array($data['error'])) {
                return ['error' => $data['error']];
            }
        }

        return $this->buildError('HTTP_ERROR', 'HTTP ' . $response->getStatusCode());
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

    private function normalizeFormat(?string $format): ?string
    {
        $value = $format ?? 'png';
        if (!in_array($value, self::SUPPORTED_FORMATS, true)) {
            return null;
        }
        return $value;
    }
}

