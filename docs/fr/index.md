# easy-hcaptcha — Documentation

> **Langues :** [English](../en/index.md) · **Français** · [Español](../es/index.md)

Un bundle Symfony moderne et sans configuration pour [hCaptcha](https://www.hcaptcha.com/).

## Sommaire

1. [Installation](installation.md)
2. [Configuration](configuration.md)
3. [Utilisation dans les templates Twig](usage-twig.md)
4. [Utilisation comme Form Type](usage-form-type.md)
5. [Utilisation comme contrainte de validation](usage-validator.md)
6. [Vérification manuelle dans un contrôleur](usage-controller.md)
7. [Migration depuis la 1.x](upgrade-from-1.x.md)

## En un coup d'œil

```yaml
# config/packages/easy_hcaptcha.yaml
easy_hcaptcha:
    site_key:   '%env(HCAPTCHA_SITE_KEY)%'
    secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

```twig
<head>{{ hcaptcha_script() }}</head>
<form method="post">
    {{ hcaptcha_widget() }}
    <button>Envoyer</button>
</form>
```

```php
$result = $verifier->verify($request->request->get('h-captcha-response'));
if ($result->isSuccess()) {
    // le formulaire est légitime
}
```

## Pourquoi ce bundle ?

- **Choisissez votre niveau d'intégration** — appel direct dans le contrôleur,
  Form Type ou contrainte sur DTO.
- **Adapté aux tests** — passez `enabled: false` et le verifier renvoie un
  succès sans aucun appel réseau.
- **Sécurisé par défaut** — URL de vérification HTTPS uniquement, allow-list
  pour les noms d'attribut du widget Twig, validation des identifiants de
  callback JS dans le script.
- **Objet réponse riche** — récupérez le hostname hCaptcha, les codes d'erreur,
  le score Enterprise et la raison sans re-parser le JSON.

## Support

Signalez les problèmes sur <https://github.com/ethsam/easy-hcaptcha/issues>.
