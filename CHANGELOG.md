# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/camelmailer/camelmailer-php/compare/v0.2.1...HEAD
[0.2.1]: https://github.com/camelmailer/camelmailer-php/releases/tag/v0.2.1
[0.2.0]: https://github.com/camelmailer/camelmailer-php/releases/tag/v0.2.0
[0.1.0]: https://github.com/camelmailer/camelmailer-php/releases/tag/v0.1.0
