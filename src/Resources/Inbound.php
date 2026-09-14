<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * Inbound and held messages (`/api/v2/server/inbound`).
 *
 * Covers mail arriving through an inbound route as well as outbound mail
 * the spam filter put on hold, which is why a message here can be either
 * retried or released past the hold.
 */
final class Inbound extends Resource
{
    /**
     * Search inbound and held messages, newest first.
     *
     * @param  array<string, int|string>  $params
     */
    public function list(array $params = []): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/inbound', query: $params);
    }

    /**
     * Retrieve one inbound message.
     */
    public function get(int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/inbound/{$id}");
    }

    /**
     * Put a message back on the delivery queue, for instance after fixing
     * the route it should have matched.
     */
    public function retry(int $id): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/inbound/{$id}/retry");
    }

    /**
     * Release a held message past the hold and deliver it.
     */
    public function bypass(int $id): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/inbound/{$id}/bypass");
    }
}
