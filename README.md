# Camelmailer PHP SDK

[![CI](https://github.com/camelmailer/camelmailer-php/actions/workflows/ci.yml/badge.svg)](https://github.com/camelmailer/camelmailer-php/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

PHP SDK for [Camelmailer](https://camelmailer.com) — transactional email, nothing else.

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

The client defaults to the Camelmailer cloud (`https://app.camelmailer.com`). Point it at your own instance:

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

// Batch: one result per entry, in the order you passed them
$camelmailer->emails->sendBatch([
    ['from' => 'a@acme.com', 'to' => ['x@example.com'], 'subject' => 'One'],
    ['from' => 'a@acme.com', 'to' => ['y@example.com'], 'subject' => 'Two'],
]);

// Retry-safe sends: the same key with the same body returns the first
// result instead of sending twice. A different body under the same key is
// refused with `InvalidIdempotentRequest`.
$camelmailer->emails->send([
    'from' => 'billing@acme.com',
    'to' => ['ada@example.com'],
    'subject' => 'Your receipt',
], idempotencyKey: 'receipt-'.$orderId);
// All four send methods take it: send, sendBatch, sendWithTemplate and
// sendWithTemplateBatch.

// Broadcast to everyone subscribed to a stream (up to 1000 per call;
// the response counts `queued` against `skipped`)
$camelmailer->emails->sendToStream('newsletter', [
    'from' => 'news@acme.com',
    'subject' => 'September',
    'text_body' => 'What shipped this month.',
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

### Campaigns

A campaign is content plus an audience. The two ways to create one behave
differently, so pick deliberately:

```php
// Write it and leave it alone. Without `scheduled_at` it stays a draft;
// with one it becomes `scheduled` and the server sends it when due.
$camelmailer->campaigns->createDraft([
    'stream' => 'newsletter',
    'name' => 'September',
    'from' => 'news@acme.com',
    'subject' => 'What shipped',
    'text_body' => 'Hello.',
    'scheduled_at' => '2026-10-01T09:00:00Z',
]);

// Create and send to the stream's subscribers straight away. The send
// starts before this call returns.
$camelmailer->campaigns->createAndSend('newsletter', [
    'name' => 'September',
    'from' => 'news@acme.com',
    'subject' => 'What shipped',
    'text_body' => 'Hello.',
]);

$camelmailer->campaigns->list();
$camelmailer->campaigns->listForStream('newsletter');
$camelmailer->campaigns->get(7);                  // with `stats`
$camelmailer->campaigns->getForStream('newsletter', 7);
$camelmailer->campaigns->update(7, ['subject' => 'Corrected']);
$camelmailer->campaigns->update(7, ['scheduled_at' => null]); // back to draft
$camelmailer->campaigns->send(7);                 // now, whatever the schedule said
$camelmailer->campaigns->cancel(7);
```

### Subscribers

A broadcast send to an address that is not subscribed is refused, so this
list is the audience.

```php
$camelmailer->subscribers->list('newsletter');
$camelmailer->subscribers->add('newsletter', ['address' => 'ada@example.com']);
$camelmailer->subscribers->import('newsletter', ['ada@example.com', 'grace@example.com']);
$camelmailer->subscribers->complaint('newsletter', 'ada@example.com'); // suppress + unsubscribe
$camelmailer->subscribers->remove('newsletter', 'ada@example.com');
```

### Layouts

A layout wraps every template that uses it, so header, footer and styling
live in one place. `html_wrapper` has to embed the body with `{{{ content }}}`.

```php
$camelmailer->layouts->list();
$camelmailer->layouts->create([
    'name' => 'Default',
    'permalink' => 'default',
    'html_wrapper' => '<html><body>{{{ content }}}</body></html>',
]);
$camelmailer->layouts->get('default');
$camelmailer->layouts->update('default', ['name' => 'Main']);
$camelmailer->layouts->uploadLogo('default', 'data:image/png;base64,...')->url;
$camelmailer->layouts->delete('default');
```

### Inbound and held messages

```php
$camelmailer->inbound->list(['status' => 'held']);
$camelmailer->inbound->get(55);
$camelmailer->inbound->retry(55)->requeued;   // back on the delivery queue
$camelmailer->inbound->bypass(55)->requeued;  // release past the hold
```

### Logs

Useful when a send did not arrive and the question is whether the request
ever reached the API.

```php
$camelmailer->logs->list(['per_page' => 25]);
$camelmailer->logs->tags();
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
                         // 'SendLimitExceeded' (429): the 30-day allowance is
                         //   used up, nothing was queued
                         // 'InvalidIdempotentRequest' (409): the key was reused
                         //   for a different body
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
