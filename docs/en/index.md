# easy-hcaptcha — Documentation

> **Languages:** **English** · [Français](../fr/index.md) · [Español](../es/index.md)

A modern, zero-config Symfony bundle for [hCaptcha](https://www.hcaptcha.com/).

## Table of contents

1. [Installation](installation.md)
2. [Configuration](configuration.md)
3. [Use it in Twig templates](usage-twig.md)
4. [Use it as a Form Type](usage-form-type.md)
5. [Use it as a Validation Constraint](usage-validator.md)
6. [Verify manually in a controller](usage-controller.md)
7. [Upgrade from 1.x](upgrade-from-1.x.md)

## At a glance

```yaml
# config/packages/easy_hcaptcha.yaml
easy_hcaptcha:
    site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

```twig
<head>{{ hcaptcha_script() }}</head>
<form method="post">
    {{ hcaptcha_widget() }}
    <button>Send</button>
</form>
```

```php
$result = $verifier->verify($request->request->get('h-captcha-response'));
if ($result->isSuccess()) {
    // form is legit
}
```

## Why this bundle?

- **Pick your integration level** — pure controller call, Form Type, or DTO constraint.
- **Test-friendly** — flip `enabled: false` and the verifier short-circuits to success without any HTTP call.
- **Safe by default** — HTTPS-only verify URL, attribute-name allow-list in the
  Twig widget, JS-callback identifier validation in the script include.
- **Rich response object** — get the hCaptcha hostname, error codes, Enterprise
  score and reason without re-parsing JSON.

## Support

Report issues at <https://github.com/ethsam/easy-hcaptcha/issues>.
