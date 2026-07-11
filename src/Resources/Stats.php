<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * Message counters and delivery statistics (`/api/v2/server/stats`).
 */
final class Stats extends Resource
{
    /**
     * Message counters, optionally windowed with `from` / `to`
     * (ISO 8601 date-times).
     *
     * @param  array<string, int|string>  $params
     */
    public function get(array $params = []): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/stats', query: $params);
    }

    /**
     * Delivery/queue statistics.
     */
    public function deliveries(): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/stats/deliveries');
    }
}
