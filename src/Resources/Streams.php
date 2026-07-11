<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * Message streams (`/api/v2/server/streams`).
 */
final class Streams extends Resource
{
    /**
     * List message streams.
     *
     * @param  array<string, int|string>  $params
     */
    public function list(array $params = []): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/streams', query: $params);
    }

    /**
     * Create a stream: `name` (required), `stream_type`
     * (`transactional` | `broadcast`).
     *
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): ApiObject
    {
        return $this->requestObject('POST', '/api/v2/server/streams', $params);
    }

    /**
     * Show a stream.
     */
    public function get(string $permalink): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/streams/{$permalink}");
    }

    /**
     * Update a stream.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $permalink, array $params): ApiObject
    {
        return $this->requestObject('PATCH', "/api/v2/server/streams/{$permalink}", $params);
    }

    /**
     * Archive a stream.
     */
    public function archive(string $permalink): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/streams/{$permalink}/archive");
    }
}
