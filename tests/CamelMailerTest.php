<?php

declare(strict_types=1);

namespace CamelMailer\Tests;

use CamelMailer\CamelMailer;
use CamelMailer\Client;
use CamelMailer\Resources\Bounces;
use CamelMailer\Resources\Dmarc;
use CamelMailer\Resources\Emails;
use CamelMailer\Resources\Stats;
use CamelMailer\Resources\Streams;
use CamelMailer\Resources\Templates;
use CamelMailer\Tests\Fixtures\FakeHttpClient;

final class CamelMailerTest extends TestCase
{
    public function test_client_factory_returns_a_client_with_all_resources(): void
    {
        $client = CamelMailer::client('cm_test_key', httpClient: new FakeHttpClient);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(Emails::class, $client->emails);
        $this->assertInstanceOf(Templates::class, $client->templates);
        $this->assertInstanceOf(Streams::class, $client->streams);
        $this->assertInstanceOf(Stats::class, $client->stats);
        $this->assertInstanceOf(Bounces::class, $client->bounces);
        $this->assertInstanceOf(Dmarc::class, $client->dmarc);
    }

    public function test_it_uses_the_cloud_base_url_by_default(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->stats->get();

        $uri = $this->http->lastRequest()->getUri();
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('app.camelmailer.com', $uri->getHost());
    }

    public function test_the_base_url_is_configurable_for_self_hosted_instances(): void
    {
        $client = $this->fakeClient(baseUrl: 'https://mail.example.com/');
        $this->http->queueEnvelope();

        $client->stats->get();

        $uri = $this->http->lastRequest()->getUri();
        $this->assertSame('mail.example.com', $uri->getHost());
        $this->assertSame('/api/v2/server/stats', $uri->getPath());
    }

    public function test_it_sends_the_server_api_key_and_json_headers(): void
    {
        $client = $this->fakeClient('cm_secret');
        $this->http->queueEnvelope();

        $client->emails->send(['from' => 'a@b.c', 'to' => ['d@e.f']]);

        $request = $this->http->lastRequest();
        $this->assertSame('cm_secret', $request->getHeaderLine('X-Server-API-Key'));
        $this->assertSame('application/json', $request->getHeaderLine('Accept'));
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('camelmailer-php/', $request->getHeaderLine('User-Agent'));
    }

    public function test_ping_validates_the_api_key(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['pong' => true]);

        $result = $client->ping();

        $this->assertRequested('GET', '/api/v2/server/ping');
        $this->assertTrue($result['pong']);
    }
}
