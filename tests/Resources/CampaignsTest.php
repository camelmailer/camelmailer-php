<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Tests\TestCase;

final class CampaignsTest extends TestCase
{
    public function test_list(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['campaigns' => [['id' => 1, 'name' => 'September']]]);

        $result = $client->campaigns->list();

        $this->assertRequested('GET', '/api/v2/server/campaigns');
        $this->assertSame('September', $result['campaigns'][0]['name']);
    }

    public function test_list_for_stream(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['campaigns' => []]);

        $client->campaigns->listForStream('newsletter');

        $this->assertRequested('GET', '/api/v2/server/streams/newsletter/campaigns');
    }

    public function test_get(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['campaign' => ['id' => 7], 'stats' => ['sent' => 120]]);

        $result = $client->campaigns->get(7);

        $this->assertRequested('GET', '/api/v2/server/campaigns/7');
        $this->assertSame(120, $result['stats']['sent']);
    }

    public function test_get_for_stream(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['campaign' => ['id' => 7]]);

        $client->campaigns->getForStream('newsletter', 7);

        $this->assertRequested('GET', '/api/v2/server/streams/newsletter/campaigns/7');
    }

    public function test_create_draft_names_its_stream_in_the_body(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['campaign' => ['id' => 3, 'status' => 'draft']], 201);

        $result = $client->campaigns->createDraft([
            'stream' => 'newsletter',
            'name' => 'September',
            'subject' => 'What shipped',
            'from' => 'news@acme.com',
            'text_body' => 'Hello.',
        ]);

        $this->assertRequested('POST', '/api/v2/server/campaigns');
        $this->assertSame('newsletter', $this->sentJson()['stream']);
        $this->assertSame('draft', $result['campaign']['status']);
    }

    public function test_create_draft_with_a_schedule(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['campaign' => ['id' => 4, 'status' => 'scheduled']], 201);

        $result = $client->campaigns->createDraft([
            'stream' => 'newsletter',
            'name' => 'October',
            'scheduled_at' => '2026-10-01T09:00:00Z',
        ]);

        $this->assertSame('2026-10-01T09:00:00Z', $this->sentJson()['scheduled_at']);
        $this->assertSame('scheduled', $result['campaign']['status']);
    }

    public function test_create_and_send_goes_out_immediately(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['campaign' => ['id' => 5, 'status' => 'sending']], 201);

        $result = $client->campaigns->createAndSend('newsletter', [
            'name' => 'September',
            'subject' => 'What shipped',
            'from' => 'news@acme.com',
            'text_body' => 'Hello.',
        ]);

        $this->assertRequested('POST', '/api/v2/server/streams/newsletter/campaigns');
        $this->assertSame('September', $this->sentJson()['name']);
        // The stream-scoped route expands to the subscribers right away.
        $this->assertSame('sending', $result['campaign']['status']);
    }

    public function test_update_can_schedule_and_unschedule(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['campaign' => ['id' => 3, 'status' => 'scheduled']]);

        $client->campaigns->update(3, ['scheduled_at' => '2026-10-01T09:00:00Z']);

        $this->assertRequested('PATCH', '/api/v2/server/campaigns/3');
        $this->assertSame('2026-10-01T09:00:00Z', $this->sentJson()['scheduled_at']);

        $this->http->queueEnvelope(['campaign' => ['id' => 3, 'status' => 'draft']]);
        $client->campaigns->update(3, ['scheduled_at' => null]);

        $this->assertNull($this->sentJson()['scheduled_at']);
    }

    public function test_send_and_cancel(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['campaign' => ['id' => 3, 'status' => 'sending']]);

        $client->campaigns->send(3);
        $this->assertRequested('POST', '/api/v2/server/campaigns/3/send');

        $this->http->queueEnvelope(['campaign' => ['id' => 3, 'status' => 'cancelled']]);
        $client->campaigns->cancel(3);
        $this->assertRequested('POST', '/api/v2/server/campaigns/3/cancel');
    }

    public function test_editing_a_sending_campaign_is_refused(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('ValidationError', 'A campaign that is sending cannot be edited.');

        $this->expectException(ErrorException::class);

        $client->campaigns->update(3, ['subject' => 'Too late']);
    }
}
