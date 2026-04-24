# Installation

> **Languages:** **English** · [Français](../fr/installation.md) · [Español](../es/installation.md)

## 1. Get an hCaptcha account

Create a free account at <https://www.hcaptcha.com/> and grab your **site key**
(public, sent to the browser) and **secret key** (server-side only).

## 2. Install the bundle

```bash
composer require ethsam/easy-hcaptcha
```

If you use **Symfony Flex**, the bundle is registered automatically and a
default `config/packages/easy_hcaptcha.yaml` is dropped for you.

If you do **not** use Flex, register the bundle manually in `config/bundles.php`:

```php
return [
    // ...
    Ethsam\EasyHcaptcha\EasyHcaptchaBundle::class => ['all' => true],
];
```

## 3. Add your keys to `.env`

```ini
HCAPTCHA_SITE_KEY=your_public_site_key
HCAPTCHA_SECRET_KEY=your_secret_key
```

## 4. Wire the configuration

Create `config/packages/easy_hcaptcha.yaml`:

```yaml
easy_hcaptcha:
    site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

That's it — every service is now available via dependency injection:

| Service id                          | Class / interface                                                       |
|-------------------------------------|-------------------------------------------------------------------------|
| `easy_hcaptcha.verifier`            | `Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface`                |
| `easy_hcaptcha.twig_extension`      | `Ethsam\EasyHcaptcha\Twig\HcaptchaExtension`                            |
| `easy_hcaptcha.form.type`           | `Ethsam\EasyHcaptcha\Form\Type\HcaptchaType`                            |
| `easy_hcaptcha.validator`           | `Ethsam\EasyHcaptcha\Validator\HcaptchaValidator`                       |

## Next steps

- [Configure](configuration.md) the bundle (theme, language, timeouts…)
- Render the widget in [Twig](usage-twig.md)
- Use it as a [Form Type](usage-form-type.md), a [Validator](usage-validator.md),
  or [verify manually](usage-controller.md) in a controller.
