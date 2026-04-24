# Migración desde 1.x a 2.0

> **Idiomas:** [English](../en/upgrade-from-1.x.md) · [Français](../fr/upgrade-from-1.x.md) · **Español**

La versión **2.0** convierte el paquete en un bundle Symfony de pleno
derecho. La migración lleva unos 5 minutos.

## Cambios incompatibles (BC breaks)

### 1. La clase se movió

| 1.x                                       | 2.0                                                |
|-------------------------------------------|----------------------------------------------------|
| `Ethsam\EasyHcaptcha\HcaptchaVerifier`    | `Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifier`    |

Si inyectabas el FQCN antiguo, cambia a la nueva interfaz (recomendado) o a
la nueva clase:

```diff
-use Ethsam\EasyHcaptcha\HcaptchaVerifier;
+use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface;

-public function __construct(HcaptchaVerifier $verifier) {}
+public function __construct(HcaptchaVerifierInterface $verifier) {}
```

### 2. Configuración movida a una clave de bundle

Antiguo `config/packages/hcaptcha.yaml`:

```yaml
parameters:
    hcaptcha_site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    hcaptcha_secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

se convierte en `config/packages/easy_hcaptcha.yaml`:

```yaml
easy_hcaptcha:
    site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

### 3. `verify()` devuelve un objeto, no un bool

```diff
-$ok = $verifier->verify($token);
-if ($ok) { /* … */ }
+$result = $verifier->verify($token);
+if ($result->isSuccess()) { /* … */ }
```

El `HcaptchaResponse` devuelto es `Stringable`, así que el **idioma antiguo**
`if ($verifier->verify($token))` sigue funcionando gracias a la coerción
truthy/falsy de PHP. Las comparaciones estrictas como `=== true` deben
migrarse.

### 4. Subida de mínimos PHP / Symfony

- PHP **8.1+** (antes 7.4)
- Symfony **5.4 / 6.4 / 7.x** (antes 4.4 / 5.x / 6.x / 7.x)

## Nuevas funcionalidades

- Funciones Twig (`hcaptcha_widget()`, `hcaptcha_script()` …)
- Form Type Symfony `HcaptchaType`
- Restricción de validación Symfony `#[Hcaptcha]`
- Objeto de valor rico `HcaptchaResponse` con score Enterprise
- Modo test (`enabled: false`) sin llamada de red
- Soporte de cliente HTTP personalizado (proxies)
- Jerarquía de excepciones tipada estrictamente
- Suite de 67 tests PHPUnit + matriz CI GitHub Actions

## Receta en tres pasos

1. `composer require ethsam/easy-hcaptcha:^2.0`
2. Mueve tu config de `parameters:` a `easy_hcaptcha:`
3. Sustituye los imports de `HcaptchaVerifier` por `HcaptchaVerifierInterface`

Y ya está. El snippet Twig de tu README antiguo se renderiza idénticamente;
el resto es opt-in.
