# Configuration

> **Languages:** **English** · [Français](../fr/configuration.md) · [Español](../es/configuration.md)

The full configuration tree, with defaults:

```yaml
easy_hcaptcha:
    site_key:        '%env(HCAPTCHA_SITE_KEY)%'    # required when enabled=true
    secret_key:      '%env(HCAPTCHA_SECRET_KEY)%'  # required when enabled=true
    verify_url:      'https://api.hcaptcha.com/siteverify'
    timeout:         5.0       # seconds, must be > 0.1
    enabled:         true      # set to false in test env
    http_client:     null      # service id of a custom HttpClientInterface

    default_widget:
        theme:    'light'      # light | dark
        size:     'normal'     # normal | compact | invisible
        language: null         # ISO 639-1 code passed as ?hl= ; null = auto
```

## Per-environment overrides

A typical setup uses production keys in `.env` and disables the bundle in tests:

```yaml
# config/packages/test/easy_hcaptcha.yaml
easy_hcaptcha:
    enabled: false   # verifier returns success without any HTTP call
```

When `enabled: false`, `site_key` and `secret_key` become optional, so you do
not need real credentials in CI.

## Custom HTTP client

If your infrastructure routes outbound traffic through a proxy, declare a
custom `HttpClientInterface` service and reference it:

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

The container build will fail with an explicit error if the referenced service
does not exist.

## Schema validation

The configuration schema enforces:

- `verify_url` must be a valid URL using `https://` (HTTP is rejected).
- `timeout` must be `> 0.1`.
- `default_widget.theme` ∈ `{light, dark}`.
- `default_widget.size` ∈ `{normal, compact, invisible}`.
- `site_key` / `secret_key` are required iff `enabled: true`.

Any violation surfaces a clear `InvalidConfigurationException` at container
compile time — you'll see it the first time you `cache:clear`.
