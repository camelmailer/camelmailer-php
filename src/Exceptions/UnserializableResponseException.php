<?php

declare(strict_types=1);

namespace CamelMailer\Exceptions;

use JsonException;

/**
 * The API returned a body that could not be decoded as JSON.
 */
final class UnserializableResponseException extends CamelMailerException
{
    public function __construct(JsonException $exception)
    {
        parent::__construct($exception->getMessage(), 0, $exception);
    }
}
