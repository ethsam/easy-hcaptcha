# Form Type

> **Idiomas:** [English](../en/usage-form-type.md) · [Français](../fr/usage-form-type.md) · **Español**

`HcaptchaType` es un tipo de formulario Symfony que:

- renderiza el widget `<div class="h-captcha">`,
- es **no mapeado** (`mapped: false`) por defecto — no escribe en tu entidad,
- viene con una restricción `Hcaptcha` **ya adjunta**, así que el formulario
  es inválido si el captcha falla.

## Ejemplo básico

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

En tu plantilla:

```twig
{{ form_start(form) }}
    {{ form_row(form.email) }}
    {{ form_row(form.message) }}
    {{ form_widget(form.captcha) }}
    <button>Enviar</button>
{{ form_end(form) }}
```

En tu controlador, el flujo estándar `$form->isSubmitted() && $form->isValid()`
ya incluye la verificación del captcha — el validador se ejecuta como parte
de la validación del formulario.

## Opciones del campo

| Opción       | Por defecto    | Notas                                              |
|--------------|----------------|----------------------------------------------------|
| `theme`      | desde config   | `'light'` o `'dark'`                               |
| `size`       | desde config   | `'normal'`, `'compact'` o `'invisible'`            |
| `language`   | desde config   | ISO 639-1 (`'es'`, `'fr'`…)                        |
| `invisible`  | `false`        | Fuerza `size` a `'invisible'`                      |
| `site_key`   | desde config   | Sobreescribe la clave de sitio (multi-tenant)      |
| `constraints`| `[Hcaptcha]`   | Pasa `[]` para desactivar la auto-validación       |

```php
->add('captcha', HcaptchaType::class, [
    'theme'    => 'dark',
    'language' => 'es',
])
```

## Tema de formulario personalizado

Se incluye un tema de formulario por defecto en
`@EasyHcaptcha/Form/hcaptcha_widget.html.twig`. Regístralo globalmente:

```yaml
# config/packages/twig.yaml
twig:
    form_themes:
        - '@EasyHcaptcha/Form/hcaptcha_widget.html.twig'
```

O por plantilla:

```twig
{% form_theme form '@EasyHcaptcha/Form/hcaptcha_widget.html.twig' %}
```

## Cómo llega el token al validador

La librería JS de hCaptcha inyecta un `<input name="h-captcha-response">`
dentro del elemento `<form>` cuando el usuario resuelve el challenge. El
validador lo recoge transparentemente desde la petición, así que aunque tu
campo de formulario tenga otro nombre (p. ej. `contact_form[captcha]`), la
validación funciona.
