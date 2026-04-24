# Verificación manual en un controlador

> **Idiomas:** [English](../en/usage-controller.md) · [Français](../fr/usage-controller.md) · **Español**

Cuando quieras control total — por ejemplo para cortar un flujo, loguear el
hostname de hCaptcha o inspeccionar el score Enterprise — inyecta
`HcaptchaVerifierInterface` directamente.

## Ejemplo mínimo

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
            return new Response('Captcha inválido: '.implode(', ', $result->getErrorCodes()), 400);
        }

        // Procesar el formulario.
        return new Response('OK');
    }
}
```

## El objeto de valor `HcaptchaResponse`

`verify()` devuelve un `HcaptchaResponse` inmutable con:

| Propiedad       | Tipo             | Descripción                                              |
|-----------------|------------------|----------------------------------------------------------|
| `success`       | `bool`           | True si el token es válido                               |
| `errorCodes`    | `list<string>`   | Códigos de error hCaptcha (`invalid-input-response`, …)  |
| `hostname`      | `?string`        | Hostname declarado en la clave de sitio                  |
| `challengeTs`   | `?string`        | Timestamp ISO-8601 del challenge                         |
| `score`         | `?float`         | Enterprise: puntuación de riesgo, `0.0` (bajo) → `1.0`   |
| `scoreReason`   | `?list<string>`  | Enterprise: razones que influyen en la puntuación        |
| `credit`        | `?bool`          | Enterprise: si la petición consumió un crédito           |

Métodos utilitarios: `isSuccess()`, `getErrorCodes()`, `hasErrorCode(string)`,
`toArray()`, `jsonSerialize()`.

## Compatibilidad ascendente con v1

`HcaptchaResponse` implementa `Stringable`, devolviendo `'1'` en éxito y `''`
en fallo. El código escrito para v1 como

```php
if ($verifier->verify($token)) { /* … */ }
```

sigue funcionando: PHP convierte strings no vacíos en `true` y `''` en `false`.

## Errores

| Excepción                          | Cuando                                      |
|------------------------------------|---------------------------------------------|
| `HcaptchaApiException`             | Fallo de red, status no 2xx, JSON inválido  |
| `HcaptchaConfigurationException`   | Configuración ausente o inválida            |

Ambas implementan `HcaptchaExceptionInterface` para capturarlas en un único
`catch`.

## Bypass en tests

Pon `easy_hcaptcha.enabled: false` en `config/packages/test/easy_hcaptcha.yaml`
y el verifier devolverá éxito sin llamar a la red.
