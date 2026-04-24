# Changelog

All notable changes to this project are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] — 2026-04-24

First release of the full Symfony bundle rewrite. The previous 1.x line was a
single-class helper; 2.0 is a proper, zero-config Symfony bundle with first-class
form, validator and Twig integrations.

### Added

- `EasyHcaptchaBundle` with auto-config — installs via Symfony Flex with zero
  manual wiring.
- `HcaptchaVerifier` (+ `HcaptchaVerifierInterface`) with strict types and a
  `HcaptchaResponse` value object carrying `success`, `errorCodes`, `hostname`,
  `challengeTs`, Enterprise `score` / `scoreReason` and `credit`.
- Custom exception hierarchy rooted at `HcaptchaExceptionInterface`:
  `HcaptchaApiException` (transport / non-2xx / malformed JSON) and
  `HcaptchaConfigurationException` (missing secret, invalid URL, unknown
  `http_client` service).
- Symfony Form Type `HcaptchaType` with an auto-attached `Hcaptcha` constraint
  and a default form theme at `@EasyHcaptcha/Form/hcaptcha_widget.html.twig`.
- Symfony Validation Constraint `#[Hcaptcha]` + `HcaptchaValidator`. The
  validator falls back to the `h-captcha-response` parameter on the current
  request, so nested forms with prefixed field names still validate correctly.
- Twig functions: `hcaptcha_script(options)`, `hcaptcha_widget(attributes)`,
  `hcaptcha_invisible_widget(attributes)`, `hcaptcha_site_key()`.
- Bundle configuration: `site_key`, `secret_key`, `verify_url`, `timeout`,
  `enabled` (test-mode toggle with no HTTP call), `http_client` (service
  override) and `default_widget` defaults (`theme`, `size`, `language`).
- HTTPS-only `verify_url` validation, attribute-name allow-list in the Twig
  widget, and JS-identifier check on `onload` / `render` script options.
- Test suite: 67 PHPUnit tests / 139 assertions covering Response VO, Verifier
  (transport failure, non-2xx, malformed JSON, configuration errors), Validator,
  Form Type, Twig Extension, Configuration tree and bundle boot.
- GitHub Actions CI matrix — PHP 8.1 / 8.2 / 8.3 / 8.4 × Symfony 6.4 LTS / 7.4.
- Coverage job (PHP 8.3 + Xdebug) uploading clover to Codecov.
- Trilingual documentation under `docs/{en,fr,es}/` — installation, configuration,
  Twig, Form Type, Validator, Controller, upgrade-from-1.x.

### Changed

- **BREAKING** — `HcaptchaVerifier` moved from `Ethsam\EasyHcaptcha\HcaptchaVerifier`
  to `Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifier`. Inject
  `HcaptchaVerifierInterface` instead of the concrete class.
- **BREAKING** — Configuration moved from Symfony `parameters:` to the bundle
  key `easy_hcaptcha:`.
- **BREAKING** — `HcaptchaVerifier::verify()` now returns `HcaptchaResponse`
  instead of `bool`. The object implements `Stringable` (`'1'` on success,
  `''` on failure), so the legacy `if ($verifier->verify($token))` idiom keeps
  working via PHP truthy/falsy string coercion. Strict comparisons (`=== true`)
  must be migrated.
- **BREAKING** — Minimum PHP raised to **8.1** and minimum Symfony raised to
  **6.4 LTS** (previously 7.4 / 4.4).

### Required dependencies

`twig/twig`, `symfony/form`, `symfony/twig-bundle` and `symfony/validator` are
now hard runtime dependencies (previously dev-only). Composer installs them
automatically when the bundle is pulled in.

### Security

- The `verify_url` is rejected at container build time unless it uses HTTPS.
- `hcaptcha_widget()` silently drops attribute names that don't match
  `[a-zA-Z_][\w:.\-]*`, preventing attribute-injection via user-controlled keys.
- `hcaptcha_script({ onload, render })` silently drops values that aren't valid
  JavaScript identifiers (`[a-zA-Z_$][\w$]*`).

### Migration

See [docs/en/upgrade-from-1.x.md](docs/en/upgrade-from-1.x.md) (or the French /
Spanish mirrors under `docs/fr/` and `docs/es/`) for a step-by-step migration
from the 1.x single-class helper.

[Unreleased]: https://github.com/ethsam/easy-hcaptcha/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/ethsam/easy-hcaptcha/releases/tag/v2.0.0
