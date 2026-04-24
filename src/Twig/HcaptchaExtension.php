<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class HcaptchaExtension extends AbstractExtension
{
    private const SCRIPT_BASE_URL = 'https://js.hcaptcha.com/1/api.js';
    private const VALID_ATTR_NAME = '/^[a-zA-Z_][a-zA-Z0-9_:.\-]*$/';
    private const VALID_JS_IDENT = '/^[a-zA-Z_$][a-zA-Z0-9_$]*$/';

    /**
     * @param array{theme?: string, size?: string, language?: string|null} $defaultWidget
     */
    public function __construct(
        private readonly string $siteKey,
        private readonly array $defaultWidget = [],
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('hcaptcha_script', $this->renderScript(...), ['is_safe' => ['html']]),
            new TwigFunction('hcaptcha_widget', $this->renderWidget(...), ['is_safe' => ['html']]),
            new TwigFunction('hcaptcha_invisible_widget', $this->renderInvisibleWidget(...), ['is_safe' => ['html']]),
            new TwigFunction('hcaptcha_site_key', fn (): string => $this->siteKey),
        ];
    }

    /**
     * @param array<string, scalar|null> $options
     */
    public function renderScript(array $options = []): string
    {
        $params = [];
        $language = $options['hl'] ?? $this->defaultWidget['language'] ?? null;
        if ($language !== null && $language !== '') {
            $params['hl'] = (string) $language;
        }
        if (isset($options['onload'])) {
            $onload = $this->safeJsIdentifier((string) $options['onload']);
            if ($onload !== '') {
                $params['onload'] = $onload;
            }
        }
        if (isset($options['render'])) {
            $render = $this->safeJsIdentifier((string) $options['render']);
            if ($render !== '') {
                $params['render'] = $render;
            }
        }
        if (!empty($options['recaptchacompat'])) {
            $params['recaptchacompat'] = 'on';
        }

        $url = self::SCRIPT_BASE_URL;
        if ($params !== []) {
            $url .= '?'.http_build_query($params);
        }

        return \sprintf('<script src="%s" async defer></script>', htmlspecialchars($url, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8'));
    }

    /**
     * @param array<string, scalar|null> $attributes
     */
    public function renderWidget(array $attributes = []): string
    {
        $merged = array_merge(
            ['theme' => $this->defaultWidget['theme'] ?? null, 'size' => $this->defaultWidget['size'] ?? null],
            $attributes,
        );

        return $this->renderDiv($merged);
    }

    /**
     * @param array<string, scalar|null> $attributes
     */
    public function renderInvisibleWidget(array $attributes = []): string
    {
        $attributes['size'] = 'invisible';

        return $this->renderWidget($attributes);
    }

    /**
     * @param array<string, scalar|null> $attributes
     */
    private function renderDiv(array $attributes): string
    {
        $dataAttrs = ['data-sitekey' => $this->siteKey];
        $passThroughAttrs = [];

        foreach ($attributes as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $key = (string) $key;
            if (!preg_match(self::VALID_ATTR_NAME, $key)) {
                continue;
            }
            if (str_starts_with($key, 'data-') || $key === 'class' || $key === 'id') {
                $passThroughAttrs[$key] = (string) $value;
                continue;
            }
            $dataAttrs['data-'.$key] = (string) $value;
        }

        $class = ($passThroughAttrs['class'] ?? '');
        $class = trim('h-captcha '.$class);
        unset($passThroughAttrs['class']);

        $rendered = '<div class="'.htmlspecialchars($class, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8').'"';
        foreach ($passThroughAttrs as $name => $value) {
            $rendered .= ' '.$this->escapeAttr($name).'="'.$this->escapeAttr($value).'"';
        }
        foreach ($dataAttrs as $name => $value) {
            $rendered .= ' '.$this->escapeAttr($name).'="'.$this->escapeAttr($value).'"';
        }
        $rendered .= '></div>';

        return $rendered;
    }

    private function escapeAttr(string $value): string
    {
        return htmlspecialchars($value, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
    }

    private function safeJsIdentifier(string $value): string
    {
        return preg_match(self::VALID_JS_IDENT, $value) ? $value : '';
    }
}
