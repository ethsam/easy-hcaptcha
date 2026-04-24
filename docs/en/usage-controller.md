# Manual verification in a controller

> **Languages:** **English** · [Français](../fr/usage-controller.md) · [Español](../es/usage-controller.md)

When you need full control — for example to short-circuit a flow, to log the
hCaptcha hostname, or to inspect the Enterprise score — inject
`HcaptchaVerifierInterface` directly.

## Minimal example

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
            return new Response('Captcha failed: '.implode(', ', $result->getErrorCodes()), 400);
        }

        // Process the form.
        return new Response('OK');
    }
}
```

## The `HcaptchaResponse` value object

`verify()` returns an immutable `HcaptchaResponse` with:

| Property        | Type             | Description                                              |
|-----------------|------------------|----------------------------------------------------------|
| `success`       | `bool`           | True if the token is valid                               |
| `errorCodes`    | `list<string>`   | hCaptcha error codes (`invalid-input-response`, …)       |
| `hostname`      | `?string`        | Hostname declared in the site key                        |
| `challengeTs`   | `?string`        | ISO-8601 timestamp of the challenge                      |
| `score`         | `?float`         | Enterprise: risk score, `0.0` (low) → `1.0` (high)      |
| `scoreReason`   | `?list<string>`  | Enterprise: reasons that influenced the score            |
| `credit`        | `?bool`          | Enterprise: whether the request consumed a credit        |

Helper methods: `isSuccess()`, `getErrorCodes()`, `hasErrorCode(string)`,
`toArray()`, `jsonSerialize()`.

## Backward compatibility with v1

`HcaptchaResponse` implements `Stringable`, returning `'1'` on success and
`''` on failure. Code written for v1 such as

```php
if ($verifier->verify($token)) { /* … */ }
```

keeps working: PHP coerces non-empty strings to `true` and `''` to `false`.

## Errors

| Exception                          | When                                        |
|------------------------------------|---------------------------------------------|
| `HcaptchaApiException`             | Network failure, non-2xx status, bad JSON   |
| `HcaptchaConfigurationException`   | Missing/invalid configuration               |

Both implement `HcaptchaExceptionInterface` so you can catch them all in one
`catch` block.

## Bypass in tests

Set `easy_hcaptcha.enabled: false` in `config/packages/test/easy_hcaptcha.yaml`
and the verifier short-circuits to a successful response without hitting the
network.
