# Validation Constraint

> **Languages:** **English** · [Français](../fr/usage-validator.md) · [Español](../es/usage-validator.md)

When you want to validate a hCaptcha token outside a Symfony Form (a custom
DTO, an API payload, a JSON body…), use the `#[Hcaptcha]` constraint.

## On a property

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

Then in your controller:

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

## Custom messages

```php
#[Hcaptcha(
    message: 'Please complete the captcha to continue.',
    apiFailureMessage: 'Captcha service is currently unreachable, please retry.',
)]
public ?string $captchaToken = null;
```

| Parameter            | Used when                                                    |
|----------------------|--------------------------------------------------------------|
| `message`            | The captcha was attempted but rejected by hCaptcha           |
| `apiFailureMessage`  | The hCaptcha API itself is unreachable (network, 5xx…)       |

The first message also receives a `{{ error_codes }}` placeholder containing
the comma-separated list of [hCaptcha error codes](https://docs.hcaptcha.com/#siteverify-error-codes)
returned by the API.

## On validation groups

Standard Symfony groups are supported:

```php
#[Hcaptcha(groups: ['signup'])]
public ?string $captchaToken = null;
```

## Token discovery

If the property value is `null` or `''`, the validator falls back to the
`h-captcha-response` parameter on the current `Request` (whatever name your
DTO field uses), which is what the hCaptcha JS library actually posts.
