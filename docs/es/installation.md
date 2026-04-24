# Instalación

> **Idiomas:** [English](../en/installation.md) · [Français](../fr/installation.md) · **Español**

## 1. Crear una cuenta hCaptcha

Crea una cuenta gratuita en <https://www.hcaptcha.com/> y obtén tu **clave de
sitio** (pública, enviada al navegador) y tu **clave secreta** (solo del lado
servidor).

## 2. Instalar el bundle

```bash
composer require ethsam/easy-hcaptcha
```

Si usas **Symfony Flex**, el bundle se registra automáticamente y se crea
para ti un `config/packages/easy_hcaptcha.yaml` por defecto.

Sin Flex, registra el bundle manualmente en `config/bundles.php`:

```php
return [
    // ...
    Ethsam\EasyHcaptcha\EasyHcaptchaBundle::class => ['all' => true],
];
```

## 3. Añadir tus claves a `.env`

```ini
HCAPTCHA_SITE_KEY=tu_clave_publica
HCAPTCHA_SECRET_KEY=tu_clave_secreta
```

## 4. Conectar la configuración

Crea `config/packages/easy_hcaptcha.yaml`:

```yaml
easy_hcaptcha:
    site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

Listo — todos los servicios están disponibles vía inyección de dependencias:

| Identificador de servicio           | Clase / interfaz                                                         |
|-------------------------------------|--------------------------------------------------------------------------|
| `easy_hcaptcha.verifier`            | `Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface`                 |
| `easy_hcaptcha.twig_extension`      | `Ethsam\EasyHcaptcha\Twig\HcaptchaExtension`                             |
| `easy_hcaptcha.form.type`           | `Ethsam\EasyHcaptcha\Form\Type\HcaptchaType`                             |
| `easy_hcaptcha.validator`           | `Ethsam\EasyHcaptcha\Validator\HcaptchaValidator`                        |

## Próximos pasos

- [Configura](configuration.md) el bundle (tema, idioma, timeouts…)
- Renderiza el widget en [Twig](usage-twig.md)
- Úsalo como [Form Type](usage-form-type.md), [Validador](usage-validator.md)
  o [verifica manualmente](usage-controller.md) en un controlador.
