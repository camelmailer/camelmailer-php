<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Tests\TestCase;

final class BouncesTest extends TestCase
{
    public function test_list(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['messages' => []]);

        $client->bounces->list(['page' => 2]);

        $request = $this->assertRequested('GET', '/api/v2/server/bounces');
        $this->assertSame('page=2', $request->getUri()->getQuery());
    }

    public function test_get(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->bounces->get(31);

        $this->assertRequested('GET', '/api/v2/server/bounces/31');
    }

    public function test_missing_bounce_throws_not_found(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('NotFound', 'no such bounce', 404);

        try {
            $client->bounces->get(1);
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('NotFound', $exception->code);
        }
    }
}
