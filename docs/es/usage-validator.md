# Restricción de validación

> **Idiomas:** [English](../en/usage-validator.md) · [Français](../fr/usage-validator.md) · **Español**

Cuando quieras validar un token de hCaptcha fuera de un formulario Symfony
(un DTO custom, un payload de API, un body JSON…), usa la restricción
`#[Hcaptcha]`.

## Sobre una propiedad

```php
use Ethsam\EasyHcaptcha\Validator\Constraint\Hcaptcha;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Hcaptcha]
    public ?string $captchaToken = null;
}
```

Luego en tu controlador:

```php
public function submit(
    Request $request,
    ValidatorInterface $validator,
    SerializerInterface $serializer,
): Response {
    $dto = $serializer->deserialize($request->getContent(), ContactDto::class, 'json');
    $errors = $validator->validate($dto);

    if (count($errors) > 0) {
        return $this->json($errors, 422);
    }
    // ...
}
```

## Mensajes personalizados

```php
#[Hcaptcha(
    message: 'Por favor completa el captcha para continuar.',
    apiFailureMessage: 'El servicio captcha no está disponible, vuelve a intentarlo.',
)]
public ?string $captchaToken = null;
```

| Parámetro            | Usado cuando                                                 |
|----------------------|--------------------------------------------------------------|
| `message`            | Se intentó el captcha pero hCaptcha lo rechazó               |
| `apiFailureMessage`  | La API hCaptcha no está accesible (red, 5xx…)                |

El primer mensaje recibe también un placeholder `{{ error_codes }}` que
contiene la lista separada por comas de [códigos de error hCaptcha](https://docs.hcaptcha.com/#siteverify-error-codes)
devueltos por la API.

## Con grupos de validación

Los grupos estándar de Symfony están soportados:

```php
#[Hcaptcha(groups: ['signup'])]
public ?string $captchaToken = null;
```

## Descubrimiento del token

Si el valor de la propiedad es `null` o `''`, el validador recurre al
parámetro `h-captcha-response` de la petición actual (sea cual sea el nombre
de tu campo DTO), que es lo que la librería JS hCaptcha realmente postea.
