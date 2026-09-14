<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * The server's own request log and tag index
 * (`/api/v2/server/logs`, `/api/v2/server/tags`).
 *
 * Useful when a send did not arrive and the question is whether the request
 * ever reached the API, and with what answer.
 */
final class Logs extends Resource
{
    /**
     * List logged API requests, newest first.
     *
     * @param  array<string, int|string>  $params
     */
    public function list(array $params = []): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/logs', query: $params);
    }

    /**
     * Tags used by the server's recent messages, most used first.
     */
    public function tags(): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/tags');
    }
}
