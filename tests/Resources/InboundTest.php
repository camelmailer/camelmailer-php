<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Tests\TestCase;

final class InboundTest extends TestCase
{
    public function test_list_with_filters(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['inbound' => [], 'pagination' => ['page' => 1]]);

        $client->inbound->list(['status' => 'held', 'per_page' => 50]);

        $request = $this->assertRequested('GET', '/api/v2/server/inbound');
        $this->assertSame('status=held&per_page=50', $request->getUri()->getQuery());
    }

    public function test_get(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['message' => ['id' => 55, 'status' => 'held']]);

        $result = $client->inbound->get(55);

        $this->assertRequested('GET', '/api/v2/server/inbound/55');
        $this->assertSame('held', $result['message']['status']);
    }

    public function test_retry_and_bypass(): void
    {
        $client = $this->fakeClient();

        // The endpoint answers with `requeued`, and carries the message.
        $this->http->queueEnvelope(['requeued' => true, 'message' => ['id' => 55]]);
        $result = $client->inbound->retry(55);
        $this->assertTrue($result->requeued);
        $this->assertSame(55, $result['message']['id']);
        $this->assertRequested('POST', '/api/v2/server/inbound/55/retry');

        $this->http->queueEnvelope(['requeued' => true, 'message' => ['id' => 55]]);
        $client->inbound->bypass(55);
        $this->assertRequested('POST', '/api/v2/server/inbound/55/bypass');
    }
}
