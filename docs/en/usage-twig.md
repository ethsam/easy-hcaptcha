# Twig usage

> **Languages:** **English** · [Français](../fr/usage-twig.md) · [Español](../es/usage-twig.md)

The bundle registers four Twig functions; all of them mark their output as
HTML-safe so you can call them directly without `|raw`.

## `hcaptcha_script(options = {})`

Renders the `<script>` tag that loads the hCaptcha JavaScript library.

```twig
<head>
    {{ hcaptcha_script() }}
    {# <script src="https://js.hcaptcha.com/1/api.js" async defer></script> #}
</head>
```

Supported options:

| Key               | Effect                                                                |
|-------------------|-----------------------------------------------------------------------|
| `hl`              | Language override (ISO 639-1, e.g. `'fr'`)                            |
| `onload`          | Name of a JS function called once the API is ready                    |
| `render`          | Set to `'explicit'` for explicit rendering                            |
| `recaptchacompat` | Pass `true` to add `recaptchacompat=on`                               |

**Security:** `onload` and `render` are matched against `/^[a-zA-Z_$][\w$]*$/`
and silently dropped if they don't look like a JS identifier.

## `hcaptcha_widget(attributes = {})`

Renders the `<div class="h-captcha" …>` placeholder the JS library replaces
with the actual challenge.

```twig
{{ hcaptcha_widget() }}
{# <div class="h-captcha" data-sitekey="…" data-theme="light" data-size="normal"></div> #}

{# Override defaults per call: #}
{{ hcaptcha_widget({ theme: 'dark', size: 'compact', callback: 'onCaptchaSolved' }) }}
```

You can pass any `data-*` attribute, plus `class` and `id`. Unknown keys are
prefixed with `data-`. Attribute names that don't match `[a-zA-Z_][\w:.\-]*`
are dropped to prevent attribute injection.

## `hcaptcha_invisible_widget(attributes = {})`

Same as `hcaptcha_widget()` but forces `data-size="invisible"` — handy for
form-level captchas triggered by JS.

```twig
<button id="submit" data-callback="onSubmit">Send</button>
{{ hcaptcha_invisible_widget({ callback: 'onSubmit' }) }}
```

## `hcaptcha_site_key()`

Returns the configured site key as a plain string. Useful when you render the
widget yourself or pass the key to a JS bundle:

```twig
<script>
    window.HCAPTCHA_SITE_KEY = {{ hcaptcha_site_key()|json_encode|raw }};
</script>
```
