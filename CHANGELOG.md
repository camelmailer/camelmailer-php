# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Guzzle 8 support: `guzzlehttp/guzzle` ^7.5 || ^8.0. Laravel 13 installs
  Guzzle 8 by default, which the SDK could not be installed beside. The
  client needed no change; CI now also runs against Guzzle 7 on PHP 8.4.

## [0.2.3] - 2026-09-14

### Fixed

- `inbound` retry and bypass read `queued`. The endpoint answers with
  `requeued`, so both returned false and no error whatever happened. They
  also expose the `message` the response carries.
- The subscriber types carried a `name`. The endpoint takes an address and a
  status; a name was silently dropped, so the field promised something the
  API does not store.

## [0.2.2] - 2026-09-14

### Fixed

- The `uploadLogo()` documentation and test read `logo_url`. The endpoint
  answers with `url`, so anyone following the README read a key that is
  never there. No call was broken; only the documented shape was wrong.

## [0.2.1] - 2026-09-14

### Added

- `emails->sendWithTemplate()` takes an idempotency key. The API claims all
  four send endpoints, so leaving it off made a template send the one thing
  a retry could duplicate.

## [0.2.0] - 2026-09-14

### Fixed

- `emails->sendBatch()` and `emails->sendWithTemplateBatch()` wrapped the
  messages in `{"messages": [...]}`. The endpoint deserializes a bare JSON
  array, so every batch send was rejected before anything was queued. They
  now send the array as given.

### Added

- `campaigns`: `createDraft()`, `createAndSend()`, `list()`,
  `listForStream()`, `get()`, `getForStream()`, `update()`, `send()`,
  `cancel()`.
- `subscribers`: `list()`, `add()`, `import()`, `complaint()`, `remove()`.
- `layouts`: `list()`, `create()`, `get()`, `update()`, `delete()`,
  `uploadLogo()`.
- `inbound`: `list()`, `get()`, `retry()`, `bypass()`.
- `logs`: `list()`, `tags()`.
- `emails->sendToStream()` for broadcasting to a stream's subscribers.
- An optional `idempotencyKey` on `emails->send()`, `sendBatch()` and
  `sendWithTemplateBatch()`. It travels as the `Idempotency-Key` header,
  because the body is what the server hashes to recognise a replay.

### Changed

- `TransporterInterface::request()` takes request headers, and its `$body`
  accepts a list as well as a keyed array.

## [0.1.0] - 2026-07-11

### Added

- `CamelMailer::client()` factory with configurable base URL for self-hosted instances and PSR-18 client injection.
- Messaging resources: `emails` (send, sendBatch, sendWithTemplate, sendWithTemplateBatch, list, get, deliveries, opens, clicks, raw), `templates` (list, create, get, update, archive, render), `streams` (list, create, get, update, archive), `stats` (get, deliveries), `bounces` (list, get), `dmarc` (summary, reports, report), plus `ping()` / `server()` on the client.
- Typed exceptions with stable API error codes (`ErrorException->code`), transport and deserialization exceptions.
- Immutable `ApiObject` responses with property and `ArrayAccess` reads.

[Unreleased]: https://github.com/camelmailer/camelmailer-php/compare/v0.2.3...HEAD
[0.2.3]: https://github.com/camelmailer/camelmailer-php/releases/tag/v0.2.3
[0.2.2]: https://github.com/camelmailer/camelmailer-php/releases/tag/v0.2.2
[0.2.1]: https://github.com/camelmailer/camelmailer-php/releases/tag/v0.2.1
[0.2.0]: https://github.com/camelmailer/camelmailer-php/releases/tag/v0.2.0
[0.1.0]: https://github.com/camelmailer/camelmailer-php/releases/tag/v0.1.0
