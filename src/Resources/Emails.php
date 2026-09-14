<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * Send and inspect messages (`/api/v2/server/messages`).
 */
final class Emails extends Resource
{
    /**
     * Send a message. Requires `from` and `to`; supports `cc`, `bcc`,
     * `reply_to`, `subject`, `html_body`, `text_body`, `headers`,
     * `attachments`, `tag`, `metadata` and `stream`.
     *
     * @param  array<string, mixed>  $params
     */
    public function send(array $params, ?string $idempotencyKey = null): ApiObject
    {
        return $this->requestObject(
            'POST',
            '/api/v2/server/messages',
            $params,
            headers: $this->idempotencyHeaders($idempotencyKey),
        );
    }

    /**
     * Send the same content to every subscriber of a broadcast stream.
     *
     * Either give `subject` with a body, or a `template` permalink with an
     * optional `template_model`. The response counts `queued` against
     * `skipped`: recipients past the per-request cap of 1000 are skipped,
     * so a larger audience wants a campaign.
     *
     * @param  array<string, mixed>  $params
     */
    public function sendToStream(string $permalink, array $params): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/streams/{$permalink}/send", $params);
    }

    /**
     * Send a batch of messages; returns one result per entry.
     *
     * Sent as a bare JSON array, which is what the endpoint expects.
     *
     * @param  list<array<string, mixed>>  $messages
     */
    public function sendBatch(array $messages, ?string $idempotencyKey = null): ApiObject
    {
        return $this->requestObject(
            'POST',
            '/api/v2/server/messages/batch',
            $messages,
            headers: $this->idempotencyHeaders($idempotencyKey),
        );
    }

    /**
     * Send using a stored template: pass `template` (permalink) and
     * `template_model` alongside the usual send parameters.
     *
     * @param  array<string, mixed>  $params
     */
    public function sendWithTemplate(array $params): ApiObject
    {
        return $this->requestObject('POST', '/api/v2/server/messages/with_template', $params);
    }

    /**
     * Send a template to many recipients in one call.
     *
     * @param  list<array<string, mixed>>  $messages
     */
    public function sendWithTemplateBatch(array $messages, ?string $idempotencyKey = null): ApiObject
    {
        return $this->requestObject(
            'POST',
            '/api/v2/server/messages/with_template/batch',
            $messages,
            headers: $this->idempotencyHeaders($idempotencyKey),
        );
    }

    /**
     * List messages. Filters: `scope`, `status`, `tag`, `query`, `stream`,
     * `page`, `per_page` (max 100).
     *
     * @param  array<string, int|string>  $params
     */
    public function list(array $params = []): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/messages', query: $params);
    }

    /**
     * Show a message.
     */
    public function get(int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/messages/{$id}");
    }

    /**
     * Delivery attempts of a message.
     */
    public function deliveries(int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/messages/{$id}/deliveries");
    }

    /**
     * Open events of a message.
     */
    public function opens(int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/messages/{$id}/opens");
    }

    /**
     * Click events of a message.
     */
    public function clicks(int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/messages/{$id}/clicks");
    }

    /**
     * Raw RFC 5322 source of a message.
     */
    public function raw(int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/messages/{$id}/raw");
    }
}
