# Installation

> **Langues :** [English](../en/installation.md) · **Français** · [Español](../es/installation.md)

## 1. Créer un compte hCaptcha

Créez un compte gratuit sur <https://www.hcaptcha.com/> et récupérez votre
**clé de site** (publique, envoyée au navigateur) et votre **clé secrète**
(uniquement côté serveur).

## 2. Installer le bundle

```bash
composer require ethsam/easy-hcaptcha
```

Si vous utilisez **Symfony Flex**, le bundle est enregistré automatiquement
et un fichier `config/packages/easy_hcaptcha.yaml` par défaut est créé pour
vous.

Sans Flex, déclarez le bundle manuellement dans `config/bundles.php` :

```php
return [
    // ...
    Ethsam\EasyHcaptcha\EasyHcaptchaBundle::class => ['all' => true],
];
```

## 3. Ajouter vos clés dans `.env`

```ini
HCAPTCHA_SITE_KEY=votre_cle_publique
HCAPTCHA_SECRET_KEY=votre_cle_secrete
```

## 4. Brancher la configuration

Créez `config/packages/easy_hcaptcha.yaml` :

```yaml
easy_hcaptcha:
    site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

C'est tout — tous les services sont disponibles via l'injection de dépendances :

| Identifiant de service              | Classe / interface                                                       |
|-------------------------------------|--------------------------------------------------------------------------|
| `easy_hcaptcha.verifier`            | `Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface`                 |
| `easy_hcaptcha.twig_extension`      | `Ethsam\EasyHcaptcha\Twig\HcaptchaExtension`                             |
| `easy_hcaptcha.form.type`           | `Ethsam\EasyHcaptcha\Form\Type\HcaptchaType`                             |
| `easy_hcaptcha.validator`           | `Ethsam\EasyHcaptcha\Validator\HcaptchaValidator`                        |

## Étapes suivantes

- [Configurer](configuration.md) le bundle (thème, langue, timeouts…)
- Afficher le widget en [Twig](usage-twig.md)
- L'utiliser comme [Form Type](usage-form-type.md), [Validateur](usage-validator.md)
  ou [vérifier manuellement](usage-controller.md) dans un contrôleur.
