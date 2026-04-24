# Configuration

> **Langues :** [English](../en/configuration.md) · **Français** · [Español](../es/configuration.md)

L'arbre de configuration complet, avec les valeurs par défaut :

```yaml
easy_hcaptcha:
    site_key:        '%env(HCAPTCHA_SITE_KEY)%'    # requis si enabled=true
    secret_key:      '%env(HCAPTCHA_SECRET_KEY)%'  # requis si enabled=true
    verify_url:      'https://api.hcaptcha.com/siteverify'
    timeout:         5.0       # en secondes, doit être > 0.1
    enabled:         true      # passez à false en environnement de test
    http_client:     null      # id de service d'un HttpClientInterface personnalisé

    default_widget:
        theme:    'light'      # light | dark
        size:     'normal'     # normal | compact | invisible
        language: null         # code ISO 639-1 passé en ?hl= ; null = auto
```

## Surcharges par environnement

Une configuration typique utilise les vraies clés en production et désactive
le bundle en test :

```yaml
# config/packages/test/easy_hcaptcha.yaml
easy_hcaptcha:
    enabled: false   # le verifier renvoie un succès sans appel HTTP
```

Quand `enabled: false`, `site_key` et `secret_key` deviennent optionnels :
inutile d'avoir de vraies clés en CI.

## Client HTTP personnalisé

Si votre infrastructure passe par un proxy sortant, déclarez un service
`HttpClientInterface` personnalisé et référencez-le :

```yaml
# config/services.yaml
services:
    app.proxied_http_client:
        class: Symfony\Contracts\HttpClient\HttpClientInterface
        factory: ['Symfony\Component\HttpClient\HttpClient', 'create']
        arguments: [{ proxy: 'http://corp-proxy:3128' }]

# config/packages/easy_hcaptcha.yaml
easy_hcaptcha:
    site_key:    '%env(HCAPTCHA_SITE_KEY)%'
    secret_key:  '%env(HCAPTCHA_SECRET_KEY)%'
    http_client: app.proxied_http_client
```

La compilation du conteneur échouera avec un message explicite si le service
référencé n'existe pas.

## Validation du schéma

Le schéma de configuration impose :

- `verify_url` doit être une URL valide en `https://` (HTTP est rejeté).
- `timeout` doit être `> 0.1`.
- `default_widget.theme` ∈ `{light, dark}`.
- `default_widget.size` ∈ `{normal, compact, invisible}`.
- `site_key` / `secret_key` sont requis si et seulement si `enabled: true`.

Toute violation lève une `InvalidConfigurationException` claire dès la
compilation du conteneur — vous la verrez au premier `cache:clear`.
