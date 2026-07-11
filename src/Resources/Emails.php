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
    public function send(array $params): ApiObject
    {
        return $this->requestObject('POST', '/api/v2/server/messages', $params);
    }

    /**
     * Send a batch of messages; returns one result per entry.
     *
     * @param  list<array<string, mixed>>  $messages
     */
    public function sendBatch(array $messages): ApiObject
    {
        return $this->requestObject('POST', '/api/v2/server/messages/batch', ['messages' => $messages]);
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
    public function sendWithTemplateBatch(array $messages): ApiObject
    {
        return $this->requestObject('POST', '/api/v2/server/messages/with_template/batch', ['messages' => $messages]);
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
