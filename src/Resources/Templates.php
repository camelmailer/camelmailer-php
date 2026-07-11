<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * Stored templates (`/api/v2/server/templates`).
 */
final class Templates extends Resource
{
    /**
     * List templates.
     *
     * @param  array<string, int|string>  $params
     */
    public function list(array $params = []): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/templates', query: $params);
    }

    /**
     * Create a template: `name` (required), `subject`, `html_body`,
     * `text_body` — subject and bodies may contain `{{ variables }}`.
     *
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): ApiObject
    {
        return $this->requestObject('POST', '/api/v2/server/templates', $params);
    }

    /**
     * Show a template.
     */
    public function get(string $permalink): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/templates/{$permalink}");
    }

    /**
     * Update a template.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $permalink, array $params): ApiObject
    {
        return $this->requestObject('PATCH', "/api/v2/server/templates/{$permalink}", $params);
    }

    /**
     * Archive a template.
     */
    public function archive(string $permalink): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/templates/{$permalink}/archive");
    }

    /**
     * Render a template against a model without sending (preview).
     *
     * @param  array<string, mixed>  $model
     */
    public function render(string $permalink, array $model = []): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/templates/{$permalink}/render", ['template_model' => $model]);
    }
}
