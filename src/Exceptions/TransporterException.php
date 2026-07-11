<?php

declare(strict_types=1);

namespace CamelMailer\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * The HTTP transport failed before an API response could be read
 * (DNS failure, connection refused, timeout, …).
 */
final class TransporterException extends CamelMailerException
{
    public function __construct(ClientExceptionInterface $exception)
    {
        parent::__construct($exception->getMessage(), 0, $exception);
    }
}
