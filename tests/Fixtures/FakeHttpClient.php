<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Fixtures;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FakeHttpClient implements ClientInterface
{
    /** @var list<ResponseInterface|ClientExceptionInterface> */
    private array $queue = [];

    /** @var list<RequestInterface> */
    public array $requests = [];

    /**
     * @param  array<string, mixed>|string|null  $body
     */
    public function queueResponse(int $status = 200, array|string|null $body = null): void
    {
        $encoded = is_string($body) ? $body : json_encode($body ?? [], JSON_THROW_ON_ERROR);

        $this->queue[] = new Response($status, ['Content-Type' => 'application/json'], $encoded);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function queueEnvelope(array $data = [], int $status = 200): void
    {
        $this->queueResponse($status, [
            'status' => 'success',
            'time' => 0.001,
            'data' => $data,
        ]);
    }

    public function queueError(string $code, string $message, int $status = 422): void
    {
        $this->queueResponse($status, [
            'status' => 'error',
            'time' => 0.001,
            'error' => ['code' => $code, 'message' => $message],
        ]);
    }

    public function queueException(ClientExceptionInterface $exception): void
    {
        $this->queue[] = $exception;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        $next = array_shift($this->queue);

        if ($next === null) {
            return new Response(200, [], '{"status":"success","time":0.0,"data":{}}');
        }

        if ($next instanceof ClientExceptionInterface) {
            throw $next;
        }

        return $next;
    }

    public function lastRequest(): RequestInterface
    {
        $last = end($this->requests);

        if ($last === false) {
            throw new RuntimeException('No requests were recorded.');
        }

        return $last;
    }
}
