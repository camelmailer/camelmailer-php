<?php

declare(strict_types=1);

namespace CamelMailer\Exceptions;

/**
 * The API answered with an error envelope. The stable error code
 * (`Unauthorized`, `Forbidden`, `NotFound`, `ValidationError`,
 * `ParameterMissing`, …) is available as `$exception->code` and
 * `$exception->getErrorCode()`.
 *
 * Two codes are worth branching on when sending: `SendLimitExceeded` (429)
 * means the 30-day allowance is used up and nothing was queued, so a retry
 * once the window moves on will work. `InvalidIdempotentRequest` (409) means
 * an `Idempotency-Key` was reused for a different body.
 *
 * @property-read string $code
 */
final class ErrorException extends CamelMailerException
{
    public function __construct(
        private readonly string $errorCode,
        string $message,
        private readonly int $statusCode = 0,
    ) {
        parent::__construct($message);
    }

    public function __get(string $name): mixed
    {
        if ($name === 'code') {
            return $this->errorCode;
        }

        return null;
    }

    public function __isset(string $name): bool
    {
        return $name === 'code';
    }

    /**
     * The stable API error code, e.g. `ValidationError`.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * The HTTP status code of the response.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
