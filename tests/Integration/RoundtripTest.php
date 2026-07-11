<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Integration;

use CamelMailer\ApiObject;
use CamelMailer\CamelMailer;
use CamelMailer\Client;
use PHPUnit\Framework\TestCase;

/**
 * Roundtrip against a real CamelMailer instance.
 *
 * Skipped unless CAMELMAILER_API_KEY is set. Point CAMELMAILER_BASE_URL at a
 * self-hosted instance if you are not using the cloud. Never runs in CI.
 */
final class RoundtripTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $apiKey = getenv('CAMELMAILER_API_KEY');

        if ($apiKey === false || $apiKey === '') {
            $this->markTestSkipped('CAMELMAILER_API_KEY is not set.');
        }

        $baseUrl = getenv('CAMELMAILER_BASE_URL');

        $this->client = CamelMailer::client(
            $apiKey,
            baseUrl: ($baseUrl === false || $baseUrl === '') ? CamelMailer::DEFAULT_BASE_URL : $baseUrl,
        );
    }

    public function test_ping(): void
    {
        $this->assertInstanceOf(ApiObject::class, $this->client->ping());
    }

    public function test_stats_roundtrip(): void
    {
        $stats = $this->client->stats->get();

        $this->assertIsArray($stats->toArray());
    }

    public function test_streams_roundtrip(): void
    {
        $streams = $this->client->streams->list();

        $this->assertIsArray($streams->toArray());
    }

    public function test_messages_roundtrip(): void
    {
        $messages = $this->client->emails->list(['per_page' => 1]);

        $this->assertIsArray($messages->toArray());
    }
}
