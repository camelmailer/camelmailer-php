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
        // A bare JSON array. The endpoint deserializes a sequence, so a
        // {"messages": [...]} wrapper is rejected before anything is queued.
        $body = $this->sentJsonList();
        $this->assertCount(2, $body);
        $this->assertSame('x@example.com', $body[0]['to'][0]);
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
        $this->assertCount(1, $this->sentJsonList());
    }

    public function test_send_carries_the_idempotency_key_as_a_header(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['message_id' => 9], 201);

        $client->emails->send([
            'from' => 'billing@acme.com',
            'to' => ['ada@example.com'],
            'subject' => 'Your receipt',
        ], idempotencyKey: 'receipt-2026-09-14');

        $request = $this->assertRequested('POST', '/api/v2/server/messages');
        $this->assertSame('receipt-2026-09-14', $request->getHeaderLine('Idempotency-Key'));
        // The key stays out of the body, which is what the server hashes.
        $this->assertArrayNotHasKey('idempotency_key', $this->sentJson());
    }

    public function test_send_without_an_idempotency_key_sends_no_header(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['message_id' => 10], 201);

        $client->emails->send(['from' => 'a@acme.com', 'to' => ['b@example.com']]);

        $this->assertFalse($this->http->lastRequest()->hasHeader('Idempotency-Key'));
    }

    public function test_send_batch_carries_the_idempotency_key(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->emails->sendBatch(
            [['from' => 'a@acme.com', 'to' => ['x@example.com']]],
            idempotencyKey: 'batch-1',
        );

        $request = $this->assertRequested('POST', '/api/v2/server/messages/batch');
        $this->assertSame('batch-1', $request->getHeaderLine('Idempotency-Key'));
    }

    public function test_send_to_stream(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['queued' => 42, 'skipped' => 0], 202);

        $result = $client->emails->sendToStream('newsletter', [
            'from' => 'news@acme.com',
            'subject' => 'September',
            'text_body' => 'Hello.',
        ]);

        $this->assertRequested('POST', '/api/v2/server/streams/newsletter/send');
        $this->assertSame(42, $result->queued);
    }

    public function test_reused_idempotency_key_surfaces_the_api_code(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('InvalidIdempotentRequest', 'This key was used for a different request.', 409);

        try {
            $client->emails->send(['from' => 'a@acme.com', 'to' => ['b@example.com']], idempotencyKey: 'reused');
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('InvalidIdempotentRequest', $exception->getErrorCode());
            $this->assertSame(409, $exception->getStatusCode());
        }
    }

    public function test_send_limit_exceeded_surfaces_the_api_code(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('SendLimitExceeded', 'The send allowance is used up.', 429);

        try {
            $client->emails->send(['from' => 'a@acme.com', 'to' => ['b@example.com']]);
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('SendLimitExceeded', $exception->getErrorCode());
            $this->assertSame(429, $exception->getStatusCode());
        }
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
