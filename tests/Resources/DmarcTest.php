<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Tests\TestCase;

final class DmarcTest extends TestCase
{
    public function test_summary(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['summary' => ['total' => 100, 'pass' => 98]]);

        $result = $client->dmarc->summary(['domain' => 'acme.com']);

        $request = $this->assertRequested('GET', '/api/v2/server/dmarc/summary');
        $this->assertSame('domain=acme.com', $request->getUri()->getQuery());
        $this->assertSame(98, $result['summary']['pass']);
    }

    public function test_reports(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['reports' => [], 'pagination' => ['page' => 1]]);

        $client->dmarc->reports(['domain' => 'acme.com', 'page' => 1]);

        $request = $this->assertRequested('GET', '/api/v2/server/dmarc/reports');
        $this->assertSame('domain=acme.com&page=1', $request->getUri()->getQuery());
    }

    public function test_report(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['report' => ['id' => 5], 'records' => []]);

        $result = $client->dmarc->report(5);

        $this->assertRequested('GET', '/api/v2/server/dmarc/reports/5');
        $this->assertSame([], $result->records);
    }

    public function test_missing_report_throws_not_found(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('NotFound', 'no such report', 404);

        try {
            $client->dmarc->report(404);
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('NotFound', $exception->code);
        }
    }
}
