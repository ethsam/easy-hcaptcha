# easy-hcaptcha — Documentación

> **Idiomas:** [English](../en/index.md) · [Français](../fr/index.md) · **Español**

Un bundle moderno y sin configuración de Symfony para [hCaptcha](https://www.hcaptcha.com/).

## Índice

1. [Instalación](installation.md)
2. [Configuración](configuration.md)
3. [Uso en plantillas Twig](usage-twig.md)
4. [Uso como Form Type](usage-form-type.md)
5. [Uso como restricción de validación](usage-validator.md)
6. [Verificación manual en un controlador](usage-controller.md)
7. [Migración desde 1.x](upgrade-from-1.x.md)

## De un vistazo

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
    <button>Enviar</button>
</form>
```

```php
$result = $verifier->verify($request->request->get('h-captcha-response'));
if ($result->isSuccess()) {
    // el formulario es legítimo
}
```

## ¿Por qué este bundle?

- **Elige tu nivel de integración** — llamada directa en el controlador,
  Form Type o restricción sobre DTO.
- **Apto para tests** — pon `enabled: false` y el verificador devuelve éxito
  sin ninguna llamada de red.
- **Seguro por defecto** — URL de verificación solo HTTPS, allow-list de
  nombres de atributos en el widget Twig, validación de identificadores de
  callback JS en el script.
- **Objeto de respuesta rico** — recupera el hostname de hCaptcha, los códigos
  de error, la puntuación Enterprise y la razón sin reparsear el JSON.

## Soporte

Reporta problemas en <https://github.com/ethsam/easy-hcaptcha/issues>.
