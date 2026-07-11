<?php

declare(strict_types=1);

namespace CamelMailer\Tests\Resources;

use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Tests\TestCase;

final class TemplatesTest extends TestCase
{
    public function test_list(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['templates' => []]);

        $client->templates->list();

        $this->assertRequested('GET', '/api/v2/server/templates');
    }

    public function test_create(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['template' => ['permalink' => 'welcome']], 201);

        $result = $client->templates->create([
            'name' => 'Welcome',
            'subject' => 'Hello {{ name }}',
            'html_body' => '<p>Hello {{ name }}</p>',
        ]);

        $this->assertRequested('POST', '/api/v2/server/templates');
        $this->assertSame('Welcome', $this->sentJson()['name']);
        $this->assertSame('welcome', $result['template']['permalink']);
    }

    public function test_get(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->templates->get('welcome');

        $this->assertRequested('GET', '/api/v2/server/templates/welcome');
    }

    public function test_update(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->templates->update('welcome', ['subject' => 'Hi {{ name }}']);

        $this->assertRequested('PATCH', '/api/v2/server/templates/welcome');
        $this->assertSame('Hi {{ name }}', $this->sentJson()['subject']);
    }

    public function test_archive(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope();

        $client->templates->archive('welcome');

        $this->assertRequested('POST', '/api/v2/server/templates/welcome/archive');
    }

    public function test_render(): void
    {
        $client = $this->fakeClient();
        $this->http->queueEnvelope(['subject' => 'Hello Ada']);

        $result = $client->templates->render('welcome', ['name' => 'Ada']);

        $this->assertRequested('POST', '/api/v2/server/templates/welcome/render');
        $this->assertSame(['name' => 'Ada'], $this->sentJson()['template_model']);
        $this->assertSame('Hello Ada', $result->subject);
    }

    public function test_missing_template_throws_not_found(): void
    {
        $client = $this->fakeClient();
        $this->http->queueError('NotFound', 'no such template', 404);

        try {
            $client->templates->get('nope');
            $this->fail('Expected an ErrorException.');
        } catch (ErrorException $exception) {
            $this->assertSame('NotFound', $exception->code);
        }
    }
}
