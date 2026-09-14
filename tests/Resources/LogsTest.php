<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Tests\TestCase;

final class LogsTest extends TestCase
{
    public function test_list_with_filters(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['requests' => [], 'pagination' => ['page' => 1]]);

        $client->logs->list(['per_page' => 25]);

        $request = $this->assertRequested('GET', '/api/v2/server/logs');
        $this->assertSame('per_page=25', $request->getUri()->getQuery());
    }

    public function test_tags(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['tags' => [['tag' => 'receipt', 'count' => 12]]]);

        $result = $client->logs->tags();

        $this->assertRequested('GET', '/api/v2/server/tags');
        $this->assertSame(12, $result['tags'][0]['count']);
    }
}
