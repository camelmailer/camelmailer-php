<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * DMARC aggregate reports (`/api/v2/server/dmarc`).
 */
final class Dmarc extends Resource
{
    /**
     * DMARC compliance summary. Filters: `domain`, `from`, `to`.
     *
     * @param  array<string, int|string>  $params
     */
    public function summary(array $params = []): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/dmarc/summary', query: $params);
    }

    /**
     * List stored DMARC aggregate reports. Filters: `domain`, `from`,
     * `to`, `page`, `per_page`.
     *
     * @param  array<string, int|string>  $params
     */
    public function reports(array $params = []): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/dmarc/reports', query: $params);
    }

    /**
     * Show a DMARC report with its records.
     */
    public function report(int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/dmarc/reports/{$id}");
    }
}
