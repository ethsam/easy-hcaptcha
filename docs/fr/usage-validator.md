# Contrainte de validation

> **Langues :** [English](../en/usage-validator.md) · **Français** · [Español](../es/usage-validator.md)

Quand vous voulez valider un token hCaptcha en dehors d'un formulaire Symfony
(un DTO custom, un payload d'API, un body JSON…), utilisez la contrainte
`#[Hcaptcha]`.

## Sur une propriété

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

Puis dans votre contrôleur :

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

## Messages personnalisés

```php
#[Hcaptcha(
    message: 'Veuillez compléter le captcha pour continuer.',
    apiFailureMessage: 'Le service captcha est momentanément injoignable, réessayez.',
)]
public ?string $captchaToken = null;
```

| Paramètre            | Utilisé quand                                                |
|----------------------|--------------------------------------------------------------|
| `message`            | Le captcha a été tenté mais rejeté par hCaptcha              |
| `apiFailureMessage`  | L'API hCaptcha elle-même est injoignable (réseau, 5xx…)      |

Le premier message reçoit aussi un placeholder `{{ error_codes }}` contenant
la liste séparée par virgules des [codes d'erreur hCaptcha](https://docs.hcaptcha.com/#siteverify-error-codes)
renvoyés par l'API.

## Avec des groupes de validation

Les groupes Symfony standard sont supportés :

```php
#[Hcaptcha(groups: ['signup'])]
public ?string $captchaToken = null;
```

## Découverte du token

Si la valeur de la propriété est `null` ou `''`, le validateur se rabat sur
le paramètre `h-captcha-response` de la requête courante (peu importe le nom
de votre champ DTO), qui est ce que la librairie JS hCaptcha poste réellement.
