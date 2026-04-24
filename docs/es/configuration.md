# Configuración

> **Idiomas:** [English](../en/configuration.md) · [Français](../fr/configuration.md) · **Español**

El árbol de configuración completo, con valores por defecto:

```yaml
easy_hcaptcha:
    site_key:        '%env(HCAPTCHA_SITE_KEY)%'    # requerido si enabled=true
    secret_key:      '%env(HCAPTCHA_SECRET_KEY)%'  # requerido si enabled=true
    verify_url:      'https://api.hcaptcha.com/siteverify'
    timeout:         5.0       # segundos, debe ser > 0.1
    enabled:         true      # poner false en entorno de test
    http_client:     null      # id de servicio de un HttpClientInterface personalizado

    default_widget:
        theme:    'light'      # light | dark
        size:     'normal'     # normal | compact | invisible
        language: null         # código ISO 639-1 pasado como ?hl= ; null = auto
```

## Sobreescritura por entorno

Una configuración típica usa las claves reales en producción y desactiva el
bundle en tests:

```yaml
# config/packages/test/easy_hcaptcha.yaml
easy_hcaptcha:
    enabled: false   # el verifier devuelve éxito sin llamada HTTP
```

Cuando `enabled: false`, `site_key` y `secret_key` se vuelven opcionales:
no necesitas claves reales en CI.

## Cliente HTTP personalizado

Si tu infraestructura sale a través de un proxy, declara un servicio
`HttpClientInterface` personalizado y referéncialo:

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

La compilación del contenedor fallará con un mensaje explícito si el servicio
referenciado no existe.

## Validación del esquema

El esquema de configuración exige:

- `verify_url` debe ser una URL válida con `https://` (HTTP es rechazado).
- `timeout` debe ser `> 0.1`.
- `default_widget.theme` ∈ `{light, dark}`.
- `default_widget.size` ∈ `{normal, compact, invisible}`.
- `site_key` / `secret_key` son requeridos sí y solo sí `enabled: true`.

Cualquier infracción genera una `InvalidConfigurationException` clara en
tiempo de compilación del contenedor — la verás en el primer `cache:clear`.
