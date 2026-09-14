<?php

declare(strict_types=1);

namespace CamelMailer\Resources;

use CamelMailer\ApiObject;

/**
 * Template layouts (`/api/v2/server/layouts`).
 *
 * A layout wraps every template that uses it, so header, footer and styling
 * live in one place instead of in each template.
 */
final class Layouts extends Resource
{
    /**
     * List all layouts of the server.
     */
    public function list(): ApiObject
    {
        return $this->requestObject('GET', '/api/v2/server/layouts');
    }

    /**
     * Create a layout.
     *
     * `html_wrapper` has to embed the body with `{{{ content }}}`; anything
     * else is refused with `ValidationError`.
     *
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): ApiObject
    {
        return $this->requestObject('POST', '/api/v2/server/layouts', $params);
    }

    /**
     * Retrieve a layout by permalink.
     */
    public function get(string $permalink): ApiObject
    {
        return $this->requestObject('GET', "/api/v2/server/layouts/{$permalink}");
    }

    /**
     * Update a layout; only the provided fields are changed.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $permalink, array $params): ApiObject
    {
        return $this->requestObject('PATCH', "/api/v2/server/layouts/{$permalink}", $params);
    }

    /**
     * Delete a layout. Templates that referenced it fall back to no wrapper.
     */
    public function delete(string $permalink): ApiObject
    {
        return $this->requestObject('DELETE', "/api/v2/server/layouts/{$permalink}");
    }

    /**
     * Upload the layout's logo as a data URL (`data:image/png;base64,…`).
     *
     * The absolute URL to reference from the wrapper comes back under
     * `url`. It is served without authentication, because mail clients
     * fetch it without a session.
     */
    public function uploadLogo(string $permalink, string $dataUrl): ApiObject
    {
        return $this->requestObject('POST', "/api/v2/server/layouts/{$permalink}/logo", ['data_url' => $dataUrl]);
    }
}
