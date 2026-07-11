# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-07-11

### Added

- `CamelMailer::client()` factory with configurable base URL for self-hosted instances and PSR-18 client injection.
- Messaging resources: `emails` (send, sendBatch, sendWithTemplate, sendWithTemplateBatch, list, get, deliveries, opens, clicks, raw), `templates` (list, create, get, update, archive, render), `streams` (list, create, get, update, archive), `stats` (get, deliveries), `bounces` (list, get), `dmarc` (summary, reports, report), plus `ping()` / `server()` on the client.
- Typed exceptions with stable API error codes (`ErrorException->code`), transport and deserialization exceptions.
- Immutable `ApiObject` responses with property and `ArrayAccess` reads.

[Unreleased]: https://github.com/camelmailer/camelmailer-php/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/camelmailer/camelmailer-php/releases/tag/v0.1.0
