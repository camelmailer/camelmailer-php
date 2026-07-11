<?php

declare(strict_types=1);

namespace CamelMailer\Tests;

use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Exceptions\TransporterException;
use CamelMailer\Exceptions\UnserializableResponseException;
use CamelMailer\Tests\Fixtures\FakeNetworkException;
use GuzzleHttp\Psr7\Request;

final class TransporterTest extends TestCase
{
    public function test_an_error_envelope_becomes_a_typed_exception_with_a_code(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('ValidationError', 'from address is not a verified domain', 422);

        try {
            $client->emails->send(['from' => 'a@b.c', 'to' => ['d@e.f']]);
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('ValidationError', $exception->code);
            $this->assertSame('ValidationError', $exception->getErrorCode());
            $this->assertSame('from address is not a verified domain', $exception->getMessage());
            $this->assertSame(422, $exception->getStatusCode());
        }
    }

    public function test_unauthorized_errors_surface_their_code(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('Unauthorized', 'invalid API key', 401);

        try {
            $client->stats->get();
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('Unauthorized', $exception->code);
            $this->assertSame(401, $exception->getStatusCode());
        }
    }

    public function test_a_failing_status_without_an_envelope_becomes_an_http_error(): void
    {
        $client = $this->fakeClient();
        $this->http->queueResponse(502, 'Bad Gateway');

        try {
            $client->stats->get();
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('HttpError', $exception->code);
            $this->assertSame(502, $exception->getStatusCode());
        }
    }

    public function test_network_failures_become_transporter_exceptions(): void
    {
        $client = $this->fakeClient();
        $this->http->queueException(
            new FakeNetworkException(new Request('GET', 'https://app.camelmailer.com'))
        );

        $this->expectException(TransporterException::class);
        $client->stats->get();
    }

    public function test_invalid_json_on_success_becomes_an_unserializable_response(): void
    {
        $client = $this->fakeClient();
        $this->http->queueResponse(200, 'not json at all');

        $this->expectException(UnserializableResponseException::class);
        $client->stats->get();
    }

    public function test_query_parameters_are_encoded(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->emails->list(['page' => 2, 'per_page' => 50, 'query' => 'hello world']);

        $uri = $this->http->lastRequest()->getUri();
        $this->assertSame('page=2&per_page=50&query=hello%20world', $uri->getQuery());
    }

    public function test_get_requests_have_no_body(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->stats->deliveries();

        $this->assertSame('', (string) $this->http->lastRequest()->getBody());
    }
}
