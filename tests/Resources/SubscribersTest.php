<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Tests\TestCase;

final class SubscribersTest extends TestCase
{
    public function test_list(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['subscribers' => [['address' => 'ada@example.com', 'status' => 'subscribed']]]);

        $result = $client->subscribers->list('newsletter');

        $this->assertRequested('GET', '/api/v2/server/streams/newsletter/subscribers');
        $this->assertSame('subscribed', $result['subscribers'][0]['status']);
    }

    public function test_add_upserts_by_address(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['subscriber' => ['address' => 'ada@example.com']], 201);

        $client->subscribers->add('newsletter', [
            'address' => 'ada@example.com',
            'name' => 'Ada Lovelace',
        ]);

        $this->assertRequested('POST', '/api/v2/server/streams/newsletter/subscribers');
        $this->assertSame('ada@example.com', $this->sentJson()['address']);
    }

    public function test_import(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['imported' => 2, 'total' => 2]);

        $result = $client->subscribers->import('newsletter', ['ada@example.com', 'grace@example.com']);

        $this->assertRequested('POST', '/api/v2/server/streams/newsletter/subscribers/import');
        $this->assertSame(['ada@example.com', 'grace@example.com'], $this->sentJson()['addresses']);
        $this->assertSame(2, $result->imported);
    }

    public function test_remove_encodes_the_address(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['removed' => true]);

        $client->subscribers->remove('newsletter', 'ada+news@example.com');

        // The plus has to survive the path, or a different address is removed.
        $this->assertRequested(
            'DELETE',
            '/api/v2/server/streams/newsletter/subscribers/ada%2Bnews%40example.com',
        );
    }

    public function test_complaint(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['subscriber' => ['status' => 'unsubscribed']]);

        $result = $client->subscribers->complaint('newsletter', 'ada@example.com');

        $this->assertRequested(
            'POST',
            '/api/v2/server/streams/newsletter/subscribers/ada%40example.com/complaint',
        );
        $this->assertSame('unsubscribed', $result['subscriber']['status']);
    }
}
