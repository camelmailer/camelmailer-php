<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * Opt-in subscribers of a broadcast stream
 * (`/api/v2/server/streams/{permalink}/subscribers`).
 *
 * A broadcast send to an address that is not subscribed is refused, so
 * this list is the audience, not a convenience.
 */
final class Subscribers extends Resource
{
    private function base(string $permalink): string
    {
        return "/api/v2/server/streams/{$permalink}/subscribers";
    }

    /**
     * List the stream's subscribers, subscribed and unsubscribed alike.
     */
    public function list(string $permalink): ApiObject
    {
        return $this->requestObject('GET', $this->base($permalink));
    }

    /**
     * Add or update one subscriber. Upserts by address, so calling it
     * twice is safe.
     *
     * Takes `address` (required) and `status` (`subscribed` by default,
     * or `unsubscribed`). There is no name field.
     *
     * @param  array<string, mixed>  $params
     */
    public function add(string $permalink, array $params): ApiObject
    {
        return $this->requestObject('POST', $this->base($permalink), $params);
    }

    /**
     * Add many addresses at once, all as `subscribed`. Blanks and duplicates
     * within the request are skipped, and the response reports how many were
     * written against how many survived that filtering.
     *
     * @param  list<string>  $addresses
     */
    public function import(string $permalink, array $addresses): ApiObject
    {
        return $this->requestObject('POST', $this->base($permalink).'/import', ['addresses' => $addresses]);
    }

    /**
     * Remove a subscriber from the stream entirely.
     */
    public function remove(string $permalink, string $address): ApiObject
    {
        return $this->requestObject('DELETE', $this->base($permalink).'/'.rawurlencode($address));
    }

    /**
     * Record a spam complaint against an address: writes a stream-scoped
     * `complaint` suppression and flips the subscription to `unsubscribed`.
     * Idempotent, so a feedback loop can replay it safely.
     */
    public function complaint(string $permalink, string $address): ApiObject
    {
        return $this->requestObject('POST', $this->base($permalink).'/'.rawurlencode($address).'/complaint');
    }
}
