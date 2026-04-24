# Form Type

> **Languages:** **English** · [Français](../fr/usage-form-type.md) · [Español](../es/usage-form-type.md)

`HcaptchaType` is a Symfony Form type that:

- renders the `<div class="h-captcha">` widget,
- is **unmapped** (`mapped: false`) by default — it does not try to write to
  your entity,
- comes with a `Hcaptcha` constraint **already attached**, so the form is
  invalid if the captcha fails.

## Basic example

```php
use Ethsam\EasyHcaptcha\Form\Type\HcaptchaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

$form = $this->createFormBuilder()
    ->add('email', EmailType::class)
    ->add('message', TextareaType::class)
    ->add('captcha', HcaptchaType::class)
    ->getForm();
```

In your template:

```twig
{{ form_start(form) }}
    {{ form_row(form.email) }}
    {{ form_row(form.message) }}
    {{ form_widget(form.captcha) }}
    <button>Send</button>
{{ form_end(form) }}
```

In your controller, the standard `$form->isSubmitted() && $form->isValid()`
flow already includes captcha verification — the validator runs as part of
form validation.

## Per-field options

| Option       | Default     | Notes                                              |
|--------------|-------------|----------------------------------------------------|
| `theme`      | from config | `'light'` or `'dark'`                              |
| `size`       | from config | `'normal'`, `'compact'` or `'invisible'`           |
| `language`   | from config | ISO 639-1 (`'fr'`, `'es'`…)                        |
| `invisible`  | `false`     | Forces `size` to `'invisible'` regardless          |
| `site_key`   | from config | Override the site key (multi-tenant scenarios)    |
| `constraints`| `[Hcaptcha]`| Pass `[]` to disable auto-validation              |

```php
->add('captcha', HcaptchaType::class, [
    'theme'    => 'dark',
    'language' => 'es',
])
```

## Custom form theme

A default form theme is shipped at
`@EasyHcaptcha/Form/hcaptcha_widget.html.twig`. Register it globally:

```yaml
# config/packages/twig.yaml
twig:
    form_themes:
        - '@EasyHcaptcha/Form/hcaptcha_widget.html.twig'
```

Or per template:

```twig
{% form_theme form '@EasyHcaptcha/Form/hcaptcha_widget.html.twig' %}
```

## How the token reaches the validator

The hCaptcha JS library injects an `<input name="h-captcha-response">` into
the surrounding `<form>` element when the user solves the challenge. The
validator transparently picks it up from the request, so even if your form
field has a different name (e.g. `contact_form[captcha]`), validation still
works.
