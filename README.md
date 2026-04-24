# easy-hcaptcha

[![CI](https://github.com/ethsam/easy-hcaptcha/actions/workflows/ci.yml/badge.svg)](https://github.com/ethsam/easy-hcaptcha/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/ethsam/easy-hcaptcha/branch/main/graph/badge.svg)](https://codecov.io/gh/ethsam/easy-hcaptcha)
[![Latest Stable Version](https://img.shields.io/packagist/v/ethsam/easy-hcaptcha.svg)](https://packagist.org/packages/ethsam/easy-hcaptcha)
[![License](https://img.shields.io/packagist/l/ethsam/easy-hcaptcha.svg)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/ethsam/easy-hcaptcha.svg)](composer.json)

A modern, zero-config **Symfony bundle** for [hCaptcha](https://www.hcaptcha.com/):
server-side verification, Twig widget, Form Type and Validation Constraint —
ready in under a minute.

> **Languages / Langues / Idiomas:** [English](docs/en/index.md) ·
> [Français](docs/fr/index.md) · [Español](docs/es/index.md)

---

## Features

- **Zero-config** install via Symfony Flex
- Strict-typed `HcaptchaVerifier` returning a rich `HcaptchaResponse` value object
  (`success`, `errorCodes`, `hostname`, `score` for Enterprise, …)
- Symfony **Form Type** (`HcaptchaType`) with auto-attached validator
- Symfony **Validation Constraint** (`#[Hcaptcha]`) for any DTO/property
- **Twig functions**: `hcaptcha_script()`, `hcaptcha_widget()`, `hcaptcha_invisible_widget()`
- HTTPS-only verify URL, attribute name allow-list, JS callback identifier check
- Test-mode toggle (`enabled: false`) — no HTTP call, always succeeds
- PSR-3 logging on transport failures
- 100% PHP 8.1+ / Symfony 6.4 LTS · 7.x compatible

## Requirements

- PHP **8.1** or higher
- Symfony **6.4 LTS** or **7.x**

## Quick install

```bash
composer require ethsam/easy-hcaptcha
```

Then in `.env`:

```ini
HCAPTCHA_SITE_KEY=your_public_site_key
HCAPTCHA_SECRET_KEY=your_secret_key
```

And in `config/packages/easy_hcaptcha.yaml`:

```yaml
easy_hcaptcha:
    site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

## 30-second usage

```twig
{# templates/contact.html.twig #}
<head>{{ hcaptcha_script() }}</head>
<form method="post">
    {{ hcaptcha_widget() }}
    <button>Send</button>
</form>
```

```php
// In your controller — pick your favourite style:

// 1) Form Type (recommended — auto-validated)
$form = $this->createFormBuilder()
    ->add('email', EmailType::class)
    ->add('captcha', HcaptchaType::class)
    ->getForm();

// 2) Validation Constraint on a DTO
class ContactDto {
    #[\Ethsam\EasyHcaptcha\Validator\Constraint\Hcaptcha]
    public ?string $captchaToken = null;
}

// 3) Manual call (full control)
public function submit(Request $r, HcaptchaVerifierInterface $verifier): Response
{
    $result = $verifier->verify($r->request->get('h-captcha-response'), $r->getClientIp());
    if (!$result->isSuccess()) {
        return $this->redirectToRoute('contact');
    }
    // ...
}
```

## Documentation

Full documentation in three languages:

| English | Français | Español |
|---------|----------|---------|
| [📖 Index](docs/en/index.md) | [📖 Index](docs/fr/index.md) | [📖 Índice](docs/es/index.md) |
| [Installation](docs/en/installation.md) | [Installation](docs/fr/installation.md) | [Instalación](docs/es/installation.md) |
| [Configuration](docs/en/configuration.md) | [Configuration](docs/fr/configuration.md) | [Configuración](docs/es/configuration.md) |
| [Twig usage](docs/en/usage-twig.md) | [Utilisation Twig](docs/fr/usage-twig.md) | [Uso con Twig](docs/es/usage-twig.md) |
| [Form Type](docs/en/usage-form-type.md) | [Form Type](docs/fr/usage-form-type.md) | [Form Type](docs/es/usage-form-type.md) |
| [Validator](docs/en/usage-validator.md) | [Validateur](docs/fr/usage-validator.md) | [Validador](docs/es/usage-validator.md) |
| [Controller](docs/en/usage-controller.md) | [Contrôleur](docs/fr/usage-controller.md) | [Controlador](docs/es/usage-controller.md) |
| [Upgrade from 1.x](docs/en/upgrade-from-1.x.md) | [Migration depuis 1.x](docs/fr/upgrade-from-1.x.md) | [Migración desde 1.x](docs/es/upgrade-from-1.x.md) |

## Testing your app without hitting the API

In `config/packages/test/easy_hcaptcha.yaml`:

```yaml
easy_hcaptcha:
    enabled: false   # verifier returns success without any network call
```

## Contributing

Pull requests welcome. Run the test suite locally:

```bash
composer install
vendor/bin/phpunit
```

## License

Released under the [MIT License](LICENSE) — see the file for details.

Originally authored by [Samuel ETHEVE](https://github.com/ethsam).
