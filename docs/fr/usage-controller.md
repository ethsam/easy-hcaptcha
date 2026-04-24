# Vérification manuelle dans un contrôleur

> **Langues :** [English](../en/usage-controller.md) · **Français** · [Español](../es/usage-controller.md)

Quand vous voulez le contrôle total — par exemple pour court-circuiter un flux,
logger le hostname hCaptcha ou inspecter le score Enterprise — injectez
directement `HcaptchaVerifierInterface`.

## Exemple minimal

```php
use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController
{
    public function __construct(
        private readonly HcaptchaVerifierInterface $verifier,
    ) {}

    #[Route('/contact', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        $token  = $request->request->get('h-captcha-response');
        $result = $this->verifier->verify($token, $request->getClientIp());

        if (!$result->isSuccess()) {
            return new Response('Captcha invalide : '.implode(', ', $result->getErrorCodes()), 400);
        }

        // Traiter le formulaire.
        return new Response('OK');
    }
}
```

## L'objet de valeur `HcaptchaResponse`

`verify()` retourne un `HcaptchaResponse` immuable avec :

| Propriété       | Type             | Description                                              |
|-----------------|------------------|----------------------------------------------------------|
| `success`       | `bool`           | True si le token est valide                              |
| `errorCodes`    | `list<string>`   | Codes d'erreur hCaptcha (`invalid-input-response`, …)    |
| `hostname`      | `?string`        | Hostname déclaré dans la clé de site                     |
| `challengeTs`   | `?string`        | Timestamp ISO-8601 du challenge                          |
| `score`         | `?float`         | Enterprise : score de risque, `0.0` (faible) → `1.0`    |
| `scoreReason`   | `?list<string>`  | Enterprise : raisons influençant le score                |
| `credit`        | `?bool`          | Enterprise : la requête a-t-elle consommé un crédit      |

Méthodes utilitaires : `isSuccess()`, `getErrorCodes()`, `hasErrorCode(string)`,
`toArray()`, `jsonSerialize()`.

## Compatibilité ascendante avec la 1.x

`HcaptchaResponse` implémente `Stringable`, retournant `'1'` en cas de succès
et `''` en cas d'échec. Le code écrit pour la v1 comme

```php
if ($verifier->verify($token)) { /* … */ }
```

continue de fonctionner : PHP coerce les chaînes non vides en `true` et `''`
en `false`.

## Erreurs

| Exception                          | Quand                                       |
|------------------------------------|---------------------------------------------|
| `HcaptchaApiException`             | Échec réseau, statut non-2xx, JSON invalide |
| `HcaptchaConfigurationException`   | Configuration manquante ou invalide         |

Toutes deux implémentent `HcaptchaExceptionInterface` pour les attraper en
un seul `catch`.

## Bypass en environnement de test

Mettez `easy_hcaptcha.enabled: false` dans `config/packages/test/easy_hcaptcha.yaml`
et le verifier renverra un succès sans appeler le réseau.
