<?php

declare(strict_types=1);

namespace CamelMailer;

use ArrayAccess;
use JsonSerializable;
use LogicException;

/**
 * An immutable response object: attributes are readable as properties
 * (`$result->message_id`) and as array offsets (`$result['message_id']`).
 *
 * @implements ArrayAccess<string, mixed>
 */
final class ApiObject implements ArrayAccess, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    private function __construct(private readonly array $attributes) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function from(array $attributes): self
    {
        return new self($attributes);
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('CamelMailer response objects are immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('CamelMailer response objects are immutable.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->attributes;
    }
}
