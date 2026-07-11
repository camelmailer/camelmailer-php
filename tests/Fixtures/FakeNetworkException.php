<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Fixtures;

use Exception;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

final class FakeNetworkException extends Exception implements NetworkExceptionInterface
{
    public function __construct(private readonly RequestInterface $request, string $message = 'Connection refused')
    {
        parent::__construct($message);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
