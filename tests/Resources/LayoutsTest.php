<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Tests\TestCase;

final class LayoutsTest extends TestCase
{
    public function test_list(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['layouts' => [['permalink' => 'default']]]);

        $result = $client->layouts->list();

        $this->assertRequested('GET', '/api/v2/server/layouts');
        $this->assertSame('default', $result['layouts'][0]['permalink']);
    }

    public function test_create(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['layout' => ['permalink' => 'default']], 201);

        $client->layouts->create([
            'name' => 'Default',
            'permalink' => 'default',
            'html_wrapper' => '<html><body>{{{ content }}}</body></html>',
        ]);

        $this->assertRequested('POST', '/api/v2/server/layouts');
        $this->assertStringContainsString('{{{ content }}}', $this->sentJson()['html_wrapper']);
    }

    public function test_create_without_the_content_placeholder_is_refused(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('ValidationError', 'html_wrapper must contain {{{ content }}}');

        $this->expectException(ErrorException::class);

        $client->layouts->create(['name' => 'Broken', 'html_wrapper' => '<html></html>']);
    }

    public function test_get_update_and_delete(): void
    {
        $client = $this->fakeClient();

        $this->http->queueEnvelope(['layout' => ['permalink' => 'default']]);
        $client->layouts->get('default');
        $this->assertRequested('GET', '/api/v2/server/layouts/default');

        $this->http->queueEnvelope(['layout' => ['permalink' => 'default', 'name' => 'Renamed']]);
        $client->layouts->update('default', ['name' => 'Renamed']);
        $this->assertRequested('PATCH', '/api/v2/server/layouts/default');
        $this->assertSame('Renamed', $this->sentJson()['name']);

        $this->http->queueEnvelope(['deleted' => true]);
        $client->layouts->delete('default');
        $this->assertRequested('DELETE', '/api/v2/server/layouts/default');
    }

    public function test_upload_logo(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['url' => 'https://app.camelmailer.com/assets/layouts/l-1/logo']);

        $result = $client->layouts->uploadLogo('default', 'data:image/png;base64,iVBORw0KGgo=');

        $this->assertRequested('POST', '/api/v2/server/layouts/default/logo');
        $this->assertSame('data:image/png;base64,iVBORw0KGgo=', $this->sentJson()['data_url']);
        // The endpoint answers with `url`, not `logo_url`.
        $this->assertSame('https://app.camelmailer.com/assets/layouts/l-1/logo', $result->url);
    }
}
