<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;
use CamelMailer\Contracts\TransporterInterface;

abstract class Resource
{
    public function __construct(protected readonly TransporterInterface $transporter) {}

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, int|string>  $query
     */
    protected function requestObject(string $method, string $path, ?array $body = null, array $query = []): ApiObject
    {
        return ApiObject::from($this->transporter->request($method, $path, $body, $query));
    }
}
