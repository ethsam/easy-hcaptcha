# Migration depuis la 1.x vers la 2.0

> **Langues :** [English](../en/upgrade-from-1.x.md) · **Français** · [Español](../es/upgrade-from-1.x.md)

La version **2.0** transforme le package en véritable bundle Symfony. La
migration prend environ 5 minutes.

## Changements cassants (BC breaks)

### 1. La classe a été déplacée

| 1.x                                       | 2.0                                                |
|-------------------------------------------|----------------------------------------------------|
| `Ethsam\EasyHcaptcha\HcaptchaVerifier`    | `Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifier`    |

Si vous injectiez l'ancien FQCN, basculez vers la nouvelle interface
(recommandé) ou la nouvelle classe :

```diff
-use Ethsam\EasyHcaptcha\HcaptchaVerifier;
+use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface;

-public function __construct(HcaptchaVerifier $verifier) {}
+public function __construct(HcaptchaVerifierInterface $verifier) {}
```

### 2. Configuration déplacée vers une clé de bundle

Ancien `config/packages/hcaptcha.yaml` :

```yaml
parameters:
    hcaptcha_site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    hcaptcha_secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

devient `config/packages/easy_hcaptcha.yaml` :

```yaml
easy_hcaptcha:
    site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

### 3. `verify()` retourne un objet, plus un bool

```diff
-$ok = $verifier->verify($token);
-if ($ok) { /* … */ }
+$result = $verifier->verify($token);
+if ($result->isSuccess()) { /* … */ }
```

Le `HcaptchaResponse` retourné est `Stringable`, donc l'**ancien idiome**
`if ($verifier->verify($token))` continue de marcher grâce à la coercion
truthy/falsy de PHP. Les comparaisons strictes comme `=== true` doivent être
migrées.

### 4. Plancher PHP / Symfony relevé

- PHP **8.1+** (auparavant 7.4)
- Symfony **6.4 LTS / 7.x** (auparavant 4.4 / 5.x / 6.x / 7.x)

## Nouvelles fonctionnalités

- Fonctions Twig (`hcaptcha_widget()`, `hcaptcha_script()` …)
- Form Type Symfony `HcaptchaType`
- Contrainte de validation Symfony `#[Hcaptcha]`
- Objet de valeur riche `HcaptchaResponse` avec score Enterprise
- Mode test (`enabled: false`) sans appel réseau
- Support du client HTTP personnalisé (proxies)
- Hiérarchie d'exceptions strictement typée
- Suite de 67 tests PHPUnit + matrice CI GitHub Actions

## Recette en trois étapes

1. `composer require ethsam/easy-hcaptcha:^2.0`
2. Déplacez votre config de `parameters:` vers `easy_hcaptcha:`
3. Remplacez les imports `HcaptchaVerifier` par `HcaptchaVerifierInterface`

C'est tout. Le snippet Twig de votre ancien README s'affiche identiquement ;
le reste est opt-in.
