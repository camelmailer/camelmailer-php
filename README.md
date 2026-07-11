# CamelMailer PHP SDK

[![CI](https://github.com/camelmailer/camelmailer-php/actions/workflows/ci.yml/badge.svg)](https://github.com/camelmailer/camelmailer-php/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

PHP SDK for [CamelMailer](https://camelmailer.com) — transactional email, nothing else.

Using Laravel? Grab [`camelmailer/camelmailer-laravel`](https://github.com/camelmailer/camelmailer-laravel) instead — it wires this SDK into Laravel's mail system.

## Install

```bash
composer require camelmailer/camelmailer
```

Requires PHP 8.1+.

## Quickstart

```php
$camelmailer = CamelMailer\CamelMailer::client('cm_xxxxxxxx');

$result = $camelmailer->emails->send([
    'from' => 'billing@acme.com',
    'to' => ['ada@example.com'],
    'subject' => 'Your receipt',
    'html_body' => '<p>Thanks for your purchase.</p>',
]);

echo $result->message_id;
```

## Self-hosted

The client defaults to the CamelMailer cloud (`https://app.camelmailer.com`). Point it at your own instance:

```php
$camelmailer = CamelMailer\CamelMailer::client('cm_xxxxxxxx', baseUrl: 'https://mail.example.com');
```

## Usage

### Emails

```php
// Send (from/to required; cc, bcc, reply_to, headers, attachments, tag, metadata, stream supported)
$camelmailer->emails->send([
    'from' => ['email' => 'billing@acme.com', 'name' => 'Acme Billing'],
    'to' => ['ada@example.com'],
    'subject' => 'Your receipt',
    'text_body' => 'Thanks!',
    'tag' => 'receipt',
]);

// Batch
$camelmailer->emails->sendBatch([
    ['from' => 'a@acme.com', 'to' => ['x@example.com'], 'subject' => 'One'],
    ['from' => 'a@acme.com', 'to' => ['y@example.com'], 'subject' => 'Two'],
]);

// Stored templates ({{ variables }} are rendered against template_model)
$camelmailer->emails->sendWithTemplate([
    'from' => 'hello@acme.com',
    'to' => ['ada@example.com'],
    'template' => 'welcome',
    'template_model' => ['name' => 'Ada'],
]);
$camelmailer->emails->sendWithTemplateBatch([/* ... */]);

// Read
$camelmailer->emails->list(['scope' => 'outgoing', 'tag' => 'receipt', 'page' => 1]);
$camelmailer->emails->get(1234);
$camelmailer->emails->deliveries(1234);
$camelmailer->emails->opens(1234);
$camelmailer->emails->clicks(1234);
$camelmailer->emails->raw(1234);
```

### Templates

```php
$camelmailer->templates->list();
$camelmailer->templates->create(['name' => 'Welcome', 'subject' => 'Hi {{ name }}', 'html_body' => '<p>Hi {{ name }}</p>']);
$camelmailer->templates->get('welcome');
$camelmailer->templates->update('welcome', ['subject' => 'Hello {{ name }}']);
$camelmailer->templates->render('welcome', ['name' => 'Ada']); // preview without sending
$camelmailer->templates->archive('welcome');
```

### Streams

```php
$camelmailer->streams->list();
$camelmailer->streams->create(['name' => 'Broadcasts', 'stream_type' => 'broadcast']);
$camelmailer->streams->get('broadcasts');
$camelmailer->streams->update('broadcasts', ['name' => 'Newsletter']);
$camelmailer->streams->archive('broadcasts');
```

### Stats, bounces & DMARC

```php
$camelmailer->stats->get(['from' => '2026-01-01T00:00:00Z']);
$camelmailer->stats->deliveries();

$camelmailer->bounces->list();
$camelmailer->bounces->get(31);

$camelmailer->dmarc->summary(['domain' => 'acme.com']);
$camelmailer->dmarc->reports(['page' => 1]);
$camelmailer->dmarc->report(5);
```

Every call returns a `CamelMailer\ApiObject` — read it as an object or an array:

```php
$stats = $camelmailer->stats->get();
$stats->stats['sent'];    // property access
$stats['stats']['sent'];  // ArrayAccess
$stats->toArray();
```

## Error handling

The API uses stable error codes; the SDK surfaces them as typed exceptions:

```php
use CamelMailer\Exceptions\ErrorException;
use CamelMailer\Exceptions\TransporterException;

try {
    $camelmailer->emails->send([...]);
} catch (ErrorException $e) {
    $e->code;            // 'ValidationError', 'Unauthorized', 'NotFound', ...
    $e->getMessage();    // human-readable message
    $e->getStatusCode(); // HTTP status
} catch (TransporterException $e) {
    // network-level failure
}
```

## Testing your integration

The client accepts any PSR-18 HTTP client, so you can stub responses:

```php
$camelmailer = CamelMailer\CamelMailer::client('cm_test', httpClient: $yourPsr18Stub);
```

## Docs

Full API documentation: [camelmailer.com/docs](https://camelmailer.com/docs)

## License

MIT
