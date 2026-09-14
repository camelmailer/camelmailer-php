<?php

declare(strict_types=1);

namespace CamelMailer\Transporter;

use CamelMailer\CamelMailer;
use CamelMailer\Contracts\TransporterInterface;
use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Exceptions\TransporterException;
use CamelMailer\Exceptions\UnserializableResponseException;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class HttpTransporter implements TransporterInterface
{
    private readonly string $baseUrl;

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        string $baseUrl,
        private readonly string $apiKey,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function request(string $method, string $path, ?array $body = null, array $query = [], array $headers = []): array
    {
        $uri = $this->baseUrl.$path;

        if ($query !== []) {
            $uri .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $request = $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Server-API-Key', $this->apiKey)
            ->withHeader('User-Agent', 'camelmailer-php/'.CamelMailer::VERSION);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream(
                    json_encode($body, JSON_THROW_ON_ERROR),
                ));
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new TransporterException($exception);
        }

        $status = $response->getStatusCode();
        $contents = (string) $response->getBody();

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            if ($status >= 400) {
                throw new ErrorException('HttpError', 'HTTP '.$status.': '.$contents, $status);
            }

            throw new UnserializableResponseException($exception);
        }

        if (($decoded['status'] ?? null) === 'error' || isset($decoded['error'])) {
            /** @var array{code?: string, message?: string} $error */
            $error = is_array($decoded['error'] ?? null) ? $decoded['error'] : [];

            throw new ErrorException(
                $error['code'] ?? 'UnknownError',
                $error['message'] ?? 'Unknown error.',
                $status,
            );
        }

        if ($status >= 400) {
            throw new ErrorException('HttpError', 'HTTP '.$status.': '.$contents, $status);
        }

        /** @var array<string, mixed> */
        return is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
    }
}
