<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\ApiObject;
use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Tests\TestCase;

final class EmailsTest extends TestCase
{
    public function test_send(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope([
            'message_id' => 1234,
            'recipients' => [['rcpt_to' => 'ada@example.com', 'status' => 'queued', 'token' => 'tok']],
        ], 201);

        $result = $client->emails->send([
            'from' => 'billing@acme.com',
            'to' => ['ada@example.com'],
            'subject' => 'Your receipt',
            'text_body' => 'Thanks for your purchase.',
            'tag' => 'receipt',
        ]);

        $this->assertRequested('POST', '/api/v2/server/messages');
        $this->assertSame('billing@acme.com', $this->sentJson()['from']);
        $this->assertSame(['ada@example.com'], $this->sentJson()['to']);
        $this->assertInstanceOf(ApiObject::class, $result);
        $this->assertSame(1234, $result->message_id);
        $this->assertSame('queued', $result['recipients'][0]['status']);
    }

    public function test_send_batch(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->emails->sendBatch([
            ['from' => 'a@acme.com', 'to' => ['x@example.com'], 'subject' => 'One'],
            ['from' => 'a@acme.com', 'to' => ['y@example.com'], 'subject' => 'Two'],
        ]);

        $this->assertRequested('POST', '/api/v2/server/messages/batch');
        $this->assertCount(2, $this->sentJson()['messages']);
    }

    public function test_send_with_template(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['message_id' => 7], 201);

        $result = $client->emails->sendWithTemplate([
            'from' => 'hello@acme.com',
            'to' => ['ada@example.com'],
            'template' => 'welcome',
            'template_model' => ['name' => 'Ada'],
        ]);

        $this->assertRequested('POST', '/api/v2/server/messages/with_template');
        $this->assertSame('welcome', $this->sentJson()['template']);
        $this->assertSame(['name' => 'Ada'], $this->sentJson()['template_model']);
        $this->assertSame(7, $result->message_id);
    }

    public function test_send_with_template_batch(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->emails->sendWithTemplateBatch([
            ['from' => 'a@acme.com', 'to' => ['x@example.com'], 'template' => 'welcome'],
        ]);

        $this->assertRequested('POST', '/api/v2/server/messages/with_template/batch');
        $this->assertCount(1, $this->sentJson()['messages']);
    }

    public function test_list_with_filters(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['messages' => [], 'pagination' => ['page' => 1]]);

        $result = $client->emails->list([
            'scope' => 'outgoing',
            'status' => 'Sent',
            'tag' => 'receipt',
            'query' => 'ada',
            'page' => 1,
        ]);

        $request = $this->assertRequested('GET', '/api/v2/server/messages');
        $this->assertSame('scope=outgoing&status=Sent&tag=receipt&query=ada&page=1', $request->getUri()->getQuery());
        $this->assertSame([], $result->messages);
    }

    public function test_get(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['message' => ['id' => 55]]);

        $client->emails->get(55);

        $this->assertRequested('GET', '/api/v2/server/messages/55');
    }

    public function test_message_events(): void
    {
        $client = $this->fakeClient();

        $this->http->queueEnvelope();
        $client->emails->deliveries(9);
        $this->assertRequested('GET', '/api/v2/server/messages/9/deliveries');

        $this->http->queueEnvelope();
        $client->emails->opens(9);
        $this->assertRequested('GET', '/api/v2/server/messages/9/opens');

        $this->http->queueEnvelope();
        $client->emails->clicks(9);
        $this->assertRequested('GET', '/api/v2/server/messages/9/clicks');

        $this->http->queueEnvelope();
        $client->emails->raw(9);
        $this->assertRequested('GET', '/api/v2/server/messages/9/raw');
    }

    public function test_get_of_a_missing_message_throws_not_found(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('NotFound', 'no such message', 404);

        try {
            $client->emails->get(999);
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('NotFound', $exception->code);
        }
    }
}
