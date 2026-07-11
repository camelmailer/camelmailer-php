<?php

declare(strict_types=1);

namespace CamelMailer\Tests;

use CamelMailer\CamelMailer;
use CamelMailer\Client;
use CamelMailer\Tests\Fixtures\FakeHttpClient;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\RequestInterface;

abstract class TestCase extends BaseTestCase
{
    protected FakeHttpClient $http;

    protected function fakeClient(string $apiKey = 'cm_test_key', ?string $baseUrl = null): Client
    {
        $this->http = new FakeHttpClient;

        return CamelMailer::client(
            $apiKey,
            baseUrl: $baseUrl ?? CamelMailer::DEFAULT_BASE_URL,
            httpClient: $this->http,
        );
    }

    protected function assertRequested(string $method, string $path): RequestInterface
    {
        $request = $this->http->lastRequest();

        $this->assertSame($method, $request->getMethod());
        $this->assertSame($path, $request->getUri()->getPath());

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    protected function sentJson(): array
    {
        /** @var array<string, mixed> */
        return json_decode((string) $this->http->lastRequest()->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
