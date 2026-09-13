# Contributing

## Dev setup

```bash
composer install
```

## Commands

```bash
composer test              # unit tests (mocked HTTP, no network)
composer test:integration  # roundtrip against a real instance (needs CAMELMAILER_API_KEY, optional CAMELMAILER_BASE_URL)
composer lint              # Laravel Pint (check)
composer lint:fix          # Laravel Pint (fix)
composer analyse           # PHPStan, level max
```

## Conventions

- Tests first: every resource method and error path has a unit test against the mocked PSR-18 transport.
- No network in unit tests.
- PHP 8.1 compatibility (CI runs 8.1–8.4).
- New endpoints follow the OpenAPI spec of the Camelmailer API; responses are `ApiObject`s wrapping the envelope's `data`.
