<?php

declare(strict_types=1);

namespace CamelMailer\Contracts;

use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Exceptions\TransporterException;
use CamelMailer\Exceptions\UnserializableResponseException;

interface TransporterInterface
{
    /**
     * Perform an API request and return the decoded `data` payload.
     *
     * The body is JSON-encoded as given: a keyed array becomes an object,
     * a list becomes an array. The batch endpoints take a list.
     *
     * @param  array<mixed>|null  $body
     * @param  array<string, int|string>  $query
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws ErrorException when the API answers with an error envelope
     * @throws TransporterException when the HTTP transport fails
     * @throws UnserializableResponseException when the response is not valid JSON
     */
    public function request(string $method, string $path, ?array $body = null, array $query = [], array $headers = []): array;
}
