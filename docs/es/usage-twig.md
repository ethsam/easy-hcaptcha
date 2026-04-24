# Uso con Twig

> **Idiomas:** [English](../en/usage-twig.md) · [Français](../fr/usage-twig.md) · **Español**

El bundle registra cuatro funciones Twig; todas marcan su salida como
HTML-safe para que las puedas llamar directamente sin `|raw`.

## `hcaptcha_script(opciones = {})`

Renderiza la etiqueta `<script>` que carga la librería JavaScript de hCaptcha.

```twig
<head>
    {{ hcaptcha_script() }}
    {# <script src="https://js.hcaptcha.com/1/api.js" async defer></script> #}
</head>
```

Opciones soportadas:

| Clave             | Efecto                                                                  |
|-------------------|-------------------------------------------------------------------------|
| `hl`              | Forzar idioma (ISO 639-1, ej. `'es'`)                                   |
| `onload`          | Nombre de una función JS llamada cuando la API esté lista               |
| `render`          | Pon `'explicit'` para renderizado explícito                             |
| `recaptchacompat` | Pasa `true` para añadir `recaptchacompat=on`                            |

**Seguridad:** `onload` y `render` se validan contra `/^[a-zA-Z_$][\w$]*$/`
y se descartan silenciosamente si no parecen un identificador JS.

## `hcaptcha_widget(atributos = {})`

Renderiza el `<div class="h-captcha" …>` que la librería JS sustituye por el
challenge real.

```twig
{{ hcaptcha_widget() }}
{# <div class="h-captcha" data-sitekey="…" data-theme="light" data-size="normal"></div> #}

{# Sobreescribir valores por defecto puntualmente: #}
{{ hcaptcha_widget({ theme: 'dark', size: 'compact', callback: 'onCaptchaSolved' }) }}
```

Puedes pasar cualquier atributo `data-*`, además de `class` e `id`. Las
claves desconocidas se prefijan con `data-`. Los nombres de atributo que no
coinciden con `[a-zA-Z_][\w:.\-]*` se descartan para evitar inyección de
atributos.

## `hcaptcha_invisible_widget(atributos = {})`

Igual que `hcaptcha_widget()` pero fuerza `data-size="invisible"` — útil
para captchas a nivel de formulario activados por JS.

```twig
<button id="submit" data-callback="onSubmit">Enviar</button>
{{ hcaptcha_invisible_widget({ callback: 'onSubmit' }) }}
```

## `hcaptcha_site_key()`

Devuelve la clave de sitio configurada como string. Útil cuando renderizas
el widget tú mismo o pasas la clave a un bundle JS:

```twig
<script>
    window.HCAPTCHA_SITE_KEY = {{ hcaptcha_site_key()|json_encode|raw }};
</script>
```
