
# easyHcaptcha

This package provides an easy way to integrate hCaptcha into your Symfony application.

## Installation

```bash
composer require ethsam/easy-hcaptcha
```

## Configuration

Add the following to your `.env` file:

```ini
HCAPTCHA_SITE_KEY=your_site_key
HCAPTCHA_SECRET_KEY=your_secret_key
```

Add the following to `config/packages/hcaptcha.yaml`:

```yaml
parameters:
    hcaptcha_site_key: '%env(HCAPTCHA_SITE_KEY)%'
    hcaptcha_secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

## Usage

In your Twig template, include the hCaptcha widget:

```twig
<div class="h-captcha" data-sitekey="{{ hcaptcha_site_key }}"></div>
```

In your controller, verify the captcha response:

```php
use Ethsam\EasyHcaptcha\HcaptchaVerifier;
use Symfony\Component\HttpFoundation\Request;

public function formContact(Request $request, HcaptchaVerifier $hcaptchaVerifier)
{
    $captchaResponse = $request->request->get('h-captcha-response');
    $isValid = $hcaptchaVerifier->verify($captchaResponse);

    if ($isValid) {
        // Process the form
    } else {
        // Handle the error
    }
}
```
