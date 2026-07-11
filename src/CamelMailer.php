<?php

declare(strict_types=1);

namespace CamelMailer;

use CamelMailer\Transporter\HttpTransporter;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class CamelMailer
{
    public const VERSION = '0.1.0';

    public const DEFAULT_BASE_URL = 'https://app.camelmailer.com';

    /**
     * Create a CamelMailer client authenticated with a server API key.
     *
     * Pass `baseUrl` to talk to a self-hosted instance, and `httpClient`
     * to inject any PSR-18 client (used for testing, proxies, retries, …).
     */
    public static function client(
        string $apiKey,
        string $baseUrl = self::DEFAULT_BASE_URL,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ): Client {
        $factory = new HttpFactory;

        $transporter = new HttpTransporter(
            httpClient: $httpClient ?? new GuzzleClient(['http_errors' => false]),
            requestFactory: $requestFactory ?? $factory,
            streamFactory: $streamFactory ?? $factory,
            baseUrl: $baseUrl,
            apiKey: $apiKey,
        );

        return new Client($transporter);
    }
}
