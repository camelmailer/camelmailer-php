<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * Bounce messages (`/api/v2/server/bounces`).
 */
final class Bounces extends Resource
{
    /**
     * List bounce messages.
     *
     * @param  array<string, int|string>  $params
     */
    public function list(array $params = []): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/bounces', query: $params);
    }

    /**
     * Show a bounce.
     */
    public function get(int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/bounces/{$id}");
    }
}
