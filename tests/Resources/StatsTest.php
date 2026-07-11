<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Tests\TestCase;

final class StatsTest extends TestCase
{
    public function test_get(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['stats' => ['sent' => 10, 'bounced' => 1]]);

        $result = $client->stats->get();

        $this->assertRequested('GET', '/api/v2/server/stats');
        $this->assertSame(10, $result['stats']['sent']);
    }

    public function test_get_with_a_time_window(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->stats->get(['from' => '2026-01-01T00:00:00Z', 'to' => '2026-02-01T00:00:00Z']);

        $request = $this->assertRequested('GET', '/api/v2/server/stats');
        $this->assertStringContainsString('from=2026-01-01', $request->getUri()->getQuery());
    }

    public function test_deliveries(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->stats->deliveries();

        $this->assertRequested('GET', '/api/v2/server/stats/deliveries');
    }

    public function test_unauthorized(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('Unauthorized', 'invalid API key', 401);

        try {
            $client->stats->get();
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('Unauthorized', $exception->code);
        }
    }
}
