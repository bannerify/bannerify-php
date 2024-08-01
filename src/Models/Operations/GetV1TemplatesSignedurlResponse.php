<?php

/**
 * Code generated by Speakeasy (https://speakeasyapi.com). DO NOT EDIT.
 */

declare(strict_types=1);

namespace Bannerify\Bannerify\Models\Operations;


class GetV1TemplatesSignedurlResponse
{
    /**
     * HTTP response content type for this operation
     *
     * @var string $contentType
     */
    public string $contentType;

    /**
     * HTTP response status code for this operation
     *
     * @var int $statusCode
     */
    public int $statusCode;

    /**
     * Raw HTTP response; suitable for custom response parsing
     *
     * @var ?\Psr\Http\Message\ResponseInterface $rawResponse
     */
    public ?\Psr\Http\Message\ResponseInterface $rawResponse;

    public ?string $body = null;

    /**
     * The server cannot or will not process the request due to something that is perceived to be a client error (e.g., malformed request syntax, invalid request message framing, or deceptive request routing).
     *
     * @var mixed $oneOf
     */
    public mixed $oneOf = null;

    /**
     * Although the HTTP standard specifies "unauthorized", semantically this response means "unauthenticated". That is, the client must authenticate itself to get the requested response.
     *
     * @var ?\Bannerify\Bannerify\Models\Components\ErrUnauthorized $errUnauthorized
     */
    public ?\Bannerify\Bannerify\Models\Components\ErrUnauthorized $errUnauthorized = null;

    /**
     * The client does not have access rights to the content; that is, it is unauthorized, so the server is refusing to give the requested resource. Unlike 401 Unauthorized, the client's identity is known to the server.
     *
     * @var ?\Bannerify\Bannerify\Models\Components\ErrForbidden $errForbidden
     */
    public ?\Bannerify\Bannerify\Models\Components\ErrForbidden $errForbidden = null;

    /**
     * The server cannot find the requested resource. In the browser, this means the URL is not recognized. In an API, this can also mean that the endpoint is valid but the resource itself does not exist. Servers may also send this response instead of 403 Forbidden to hide the existence of a resource from an unauthorized client. This response code is probably the most well known due to its frequent occurrence on the web.
     *
     * @var ?\Bannerify\Bannerify\Models\Components\ErrNotFound $errNotFound
     */
    public ?\Bannerify\Bannerify\Models\Components\ErrNotFound $errNotFound = null;

    /**
     * This response is sent when a request conflicts with the current state of the server.
     *
     * @var ?\Bannerify\Bannerify\Models\Components\ErrConflict $errConflict
     */
    public ?\Bannerify\Bannerify\Models\Components\ErrConflict $errConflict = null;

    /**
     * The user has sent too many requests in a given amount of time ("rate limiting")
     *
     * @var ?\Bannerify\Bannerify\Models\Components\ErrTooManyRequests $errTooManyRequests
     */
    public ?\Bannerify\Bannerify\Models\Components\ErrTooManyRequests $errTooManyRequests = null;

    /**
     * The server has encountered a situation it does not know how to handle.
     *
     * @var ?\Bannerify\Bannerify\Models\Components\ErrInternalServerError $errInternalServerError
     */
    public ?\Bannerify\Bannerify\Models\Components\ErrInternalServerError $errInternalServerError = null;

    public function __construct()
    {
        $this->contentType = '';
        $this->statusCode = 0;
        $this->rawResponse = null;
        $this->body = null;
        $this->oneOf = null;
        $this->errUnauthorized = null;
        $this->errForbidden = null;
        $this->errNotFound = null;
        $this->errConflict = null;
        $this->errTooManyRequests = null;
        $this->errInternalServerError = null;
    }
}