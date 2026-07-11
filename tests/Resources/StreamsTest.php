<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Tests\TestCase;

final class StreamsTest extends TestCase
{
    public function test_list(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['streams' => []]);

        $client->streams->list();

        $this->assertRequested('GET', '/api/v2/server/streams');
    }

    public function test_create(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope([], 201);

        $client->streams->create(['name' => 'Broadcasts', 'stream_type' => 'broadcast']);

        $this->assertRequested('POST', '/api/v2/server/streams');
        $this->assertSame('broadcast', $this->sentJson()['stream_type']);
    }

    public function test_get(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->streams->get('broadcasts');

        $this->assertRequested('GET', '/api/v2/server/streams/broadcasts');
    }

    public function test_update(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->streams->update('broadcasts', ['name' => 'Newsletter']);

        $this->assertRequested('PATCH', '/api/v2/server/streams/broadcasts');
        $this->assertSame('Newsletter', $this->sentJson()['name']);
    }

    public function test_archive(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->streams->archive('broadcasts');

        $this->assertRequested('POST', '/api/v2/server/streams/broadcasts/archive');
    }

    public function test_create_without_a_name_throws_a_validation_error(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('ParameterMissing', 'name is required', 422);

        try {
            $client->streams->create([]);
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('ParameterMissing', $exception->code);
        }
    }
}
