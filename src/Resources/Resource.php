<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;
use CamelMailer\Contracts\TransporterInterface;

abstract class Resource
{
    public function __construct(protected readonly TransporterInterface $transporter) {}

    /**
     * @param  array<mixed>|null  $body
     * @param  array<string, int|string>  $query
     * @param  array<string, string>  $headers
     */
    protected function requestObject(string $method, string $path, ?array $body = null, array $query = [], array $headers = []): ApiObject
    {
        return ApiObject::from($this->transporter->request($method, $path, $body, $query, $headers));
    }

    /**
     * The header that makes a send replayable.
     *
     * The key travels as a header rather than in the body, because the body
     * is what the server hashes to recognise the same request.
     *
     * @return array<string, string>
     */
    protected function idempotencyHeaders(?string $idempotencyKey): array
    {
        return $idempotencyKey === null ? [] : ['Idempotency-Key' => $idempotencyKey];
    }
}
