# Form Type

> **Langues :** [English](../en/usage-form-type.md) · **Français** · [Español](../es/usage-form-type.md)

`HcaptchaType` est un type de formulaire Symfony qui :

- affiche le widget `<div class="h-captcha">`,
- est **non mappé** (`mapped: false`) par défaut — il n'écrit pas dans votre
  entité,
- inclut une contrainte `Hcaptcha` **déjà attachée**, donc le formulaire est
  invalide si le captcha échoue.

## Exemple basique

```php
use Ethsam\EasyHcaptcha\Form\Type\HcaptchaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

$form = $this->createFormBuilder()
    ->add('email', EmailType::class)
    ->add('message', TextareaType::class)
    ->add('captcha', HcaptchaType::class)
    ->getForm();
```

Dans votre template :

```twig
{{ form_start(form) }}
    {{ form_row(form.email) }}
    {{ form_row(form.message) }}
    {{ form_widget(form.captcha) }}
    <button>Envoyer</button>
{{ form_end(form) }}
```

Dans votre contrôleur, le flux standard `$form->isSubmitted() && $form->isValid()`
inclut déjà la vérification du captcha — le validateur s'exécute pendant la
validation du formulaire.

## Options du champ

| Option       | Défaut       | Notes                                              |
|--------------|--------------|----------------------------------------------------|
| `theme`      | depuis config| `'light'` ou `'dark'`                              |
| `size`       | depuis config| `'normal'`, `'compact'` ou `'invisible'`           |
| `language`   | depuis config| ISO 639-1 (`'fr'`, `'es'`…)                        |
| `invisible`  | `false`      | Force `size` à `'invisible'`                       |
| `site_key`   | depuis config| Surcharge la clé de site (multi-tenant)            |
| `constraints`| `[Hcaptcha]` | Passer `[]` pour désactiver l'auto-validation      |

```php
->add('captcha', HcaptchaType::class, [
    'theme'    => 'dark',
    'language' => 'fr',
])
```

## Thème de formulaire personnalisé

Un thème de formulaire par défaut est livré dans
`@EasyHcaptcha/Form/hcaptcha_widget.html.twig`. Enregistrez-le globalement :

```yaml
# config/packages/twig.yaml
twig:
    form_themes:
        - '@EasyHcaptcha/Form/hcaptcha_widget.html.twig'
```

Ou par template :

```twig
{% form_theme form '@EasyHcaptcha/Form/hcaptcha_widget.html.twig' %}
```

## Comment le token arrive jusqu'au validateur

La librairie JS hCaptcha injecte un `<input name="h-captcha-response">` dans
l'élément `<form>` environnant quand l'utilisateur résout le challenge. Le
validateur le récupère de manière transparente depuis la requête, donc même si
votre champ de formulaire a un autre nom (par ex. `contact_form[captcha]`), la
validation fonctionne.
