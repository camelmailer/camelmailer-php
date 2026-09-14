<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * Broadcast campaigns (`/api/v2/server/campaigns`).
 *
 * A campaign is content plus an audience. There are two ways to create one
 * and they behave differently: `createDraft()` writes it and waits, while
 * `createAndSend()` expands it to the stream's subscribers straight away.
 */
final class Campaigns extends Resource
{
    /**
     * List every campaign on the server, newest first.
     */
    public function list(): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/campaigns');
    }

    /**
     * List the campaigns of one broadcast stream.
     */
    public function listForStream(string $permalink): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/streams/{$permalink}/campaigns");
    }

    /**
     * Retrieve a campaign together with its statistics.
     */
    public function get(int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/campaigns/{$id}");
    }

    /**
     * Retrieve a campaign through its stream.
     */
    public function getForStream(string $permalink, int $id): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/streams/{$permalink}/campaigns/{$id}");
    }

    /**
     * Create a campaign without sending it.
     *
     * Name the audience with `stream` (a broadcast stream permalink) and
     * give `name`, `from`, `subject` and a body. Leave `scheduled_at` out
     * for a `draft`, set it to an RFC 3339 time for `scheduled`, or pass
     * `send_now: true` to send on creation.
     *
     * @param  array<string, mixed>  $params
     */
    public function createDraft(array $params): ApiObject
    {
        return $this->requestObject('POST', '/api/v2/server/campaigns', $params);
    }

    /**
     * Create a campaign on a broadcast stream and send it immediately.
     *
     * The send starts before the call returns, so there is no draft to
     * review and no schedule to set. Use `createDraft()` when the campaign
     * should wait.
     *
     * @param  array<string, mixed>  $params
     */
    public function createAndSend(string $permalink, array $params): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/streams/{$permalink}/campaigns", $params);
    }

    /**
     * Update a draft or scheduled campaign.
     *
     * Setting `scheduled_at` moves a draft to `scheduled`; passing `null`
     * clears the schedule and drops it back to `draft`. A campaign that is
     * already sending cannot be edited.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(int $id, array $params): ApiObject
    {
        return $this->requestObject('PATCH', "/api/v2/server/campaigns/{$id}", $params);
    }

    /**
     * Send a campaign now, whatever its schedule said.
     */
    public function send(int $id): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/campaigns/{$id}/send");
    }

    /**
     * Cancel a scheduled or in-flight campaign. Messages already queued
     * are not recalled.
     */
    public function cancel(int $id): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/campaigns/{$id}/cancel");
    }
}
