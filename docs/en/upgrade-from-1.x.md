# Upgrade from 1.x to 2.0

> **Languages:** **English** · [Français](../fr/upgrade-from-1.x.md) · [Español](../es/upgrade-from-1.x.md)

Version **2.0** turns the package into a proper Symfony bundle. The migration
takes about 5 minutes.

## Breaking changes

### 1. The class moved

| 1.x                                       | 2.0                                                |
|-------------------------------------------|----------------------------------------------------|
| `Ethsam\EasyHcaptcha\HcaptchaVerifier`    | `Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifier`    |

If you injected the old FQCN, switch to the new interface (recommended) or
the new class:

```diff
-use Ethsam\EasyHcaptcha\HcaptchaVerifier;
+use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface;

-public function __construct(HcaptchaVerifier $verifier) {}
+public function __construct(HcaptchaVerifierInterface $verifier) {}
```

### 2. Configuration moved to a bundle key

Old `config/packages/hcaptcha.yaml`:

```yaml
parameters:
    hcaptcha_site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    hcaptcha_secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

becomes `config/packages/easy_hcaptcha.yaml`:

```yaml
easy_hcaptcha:
    site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

### 3. `verify()` returns an object, not a bool

```diff
-$ok = $verifier->verify($token);
-if ($ok) { /* … */ }
+$result = $verifier->verify($token);
+if ($result->isSuccess()) { /* … */ }
```

The returned `HcaptchaResponse` is `Stringable`, so the **old idiom**
`if ($verifier->verify($token))` still works thanks to PHP's truthy/falsy
string coercion. Strict comparisons like `=== true` need to be migrated.

### 4. PHP / Symfony floor raised

- PHP **8.1+** (was 7.4)
- Symfony **6.4 LTS / 7.x** (was 4.4 / 5.x / 6.x / 7.x)

## New features

- Twig functions (`hcaptcha_widget()`, `hcaptcha_script()` …)
- Symfony Form Type `HcaptchaType`
- Symfony Validator constraint `#[Hcaptcha]`
- Rich `HcaptchaResponse` value object with Enterprise score
- Test mode (`enabled: false`) with no network call
- Custom HTTP client support (proxies)
- Strict-typed exception hierarchy
- 67-test PHPUnit suite + GitHub Actions CI matrix

## Recipe in three steps

1. `composer require ethsam/easy-hcaptcha:^2.0`
2. Move your config from `parameters:` to `easy_hcaptcha:`
3. Replace `HcaptchaVerifier` imports with `HcaptchaVerifierInterface`

That's it. The Twig snippet from your old README still renders identically;
the rest is opt-in.
