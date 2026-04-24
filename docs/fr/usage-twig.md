# Utilisation dans Twig

> **Langues :** [English](../en/usage-twig.md) · **Français** · [Español](../es/usage-twig.md)

Le bundle déclare quatre fonctions Twig ; toutes marquent leur sortie comme
HTML-safe pour que vous puissiez les appeler directement sans `|raw`.

## `hcaptcha_script(options = {})`

Affiche la balise `<script>` qui charge la librairie JavaScript hCaptcha.

```twig
<head>
    {{ hcaptcha_script() }}
    {# <script src="https://js.hcaptcha.com/1/api.js" async defer></script> #}
</head>
```

Options supportées :

| Clé               | Effet                                                                  |
|-------------------|------------------------------------------------------------------------|
| `hl`              | Langue forcée (ISO 639-1, ex. `'fr'`)                                  |
| `onload`          | Nom d'une fonction JS appelée quand l'API est prête                    |
| `render`          | Mettre `'explicit'` pour le rendu explicite                            |
| `recaptchacompat` | Passer `true` pour ajouter `recaptchacompat=on`                        |

**Sécurité :** `onload` et `render` sont validés contre `/^[a-zA-Z_$][\w$]*$/`
et silencieusement supprimés s'ils ne ressemblent pas à un identifiant JS.

## `hcaptcha_widget(attributs = {})`

Affiche le `<div class="h-captcha" …>` que la librairie JS remplace par le
challenge réel.

```twig
{{ hcaptcha_widget() }}
{# <div class="h-captcha" data-sitekey="…" data-theme="light" data-size="normal"></div> #}

{# Surcharger les valeurs par défaut au cas par cas : #}
{{ hcaptcha_widget({ theme: 'dark', size: 'compact', callback: 'onCaptchaSolved' }) }}
```

Vous pouvez passer n'importe quel attribut `data-*`, plus `class` et `id`.
Les clés inconnues sont préfixées par `data-`. Les noms d'attribut qui ne
correspondent pas à `[a-zA-Z_][\w:.\-]*` sont supprimés pour empêcher
l'injection d'attributs.

## `hcaptcha_invisible_widget(attributs = {})`

Identique à `hcaptcha_widget()` mais force `data-size="invisible"` — pratique
pour les captchas déclenchés par JS au niveau du formulaire.

```twig
<button id="submit" data-callback="onSubmit">Envoyer</button>
{{ hcaptcha_invisible_widget({ callback: 'onSubmit' }) }}
```

## `hcaptcha_site_key()`

Retourne la clé de site configurée sous forme de chaîne. Utile quand vous
affichez le widget vous-même ou passez la clé à un bundle JS :

```twig
<script>
    window.HCAPTCHA_SITE_KEY = {{ hcaptcha_site_key()|json_encode|raw }};
</script>
```
