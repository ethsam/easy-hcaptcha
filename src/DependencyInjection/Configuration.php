<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\DependencyInjection;

use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifier;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_hcaptcha');
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $root */
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('site_key')
                    ->defaultNull()
                    ->info('Public site key, exposed to the browser. Required when "enabled" is true.')
                ->end()
                ->scalarNode('secret_key')
                    ->defaultNull()
                    ->info('Secret key used server-side. Required when "enabled" is true.')
                ->end()
                ->scalarNode('verify_url')
                    ->defaultValue(HcaptchaVerifier::DEFAULT_VERIFY_URL)
                    ->validate()
                        ->ifTrue(static fn ($v) => !\is_string($v) || !filter_var($v, \FILTER_VALIDATE_URL))
                        ->thenInvalid('"easy_hcaptcha.verify_url" must be a valid URL, got: %s')
                    ->end()
                ->end()
                ->floatNode('timeout')
                    ->defaultValue(5.0)
                    ->min(0.1)
                    ->info('HTTP timeout in seconds for the siteverify call.')
                ->end()
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info('Set to false (typically in test env) to bypass the API and always succeed.')
                ->end()
                ->scalarNode('http_client')
                    ->defaultNull()
                    ->info('Optional service id of a pre-configured HttpClientInterface (e.g. for proxies).')
                ->end()
                ->arrayNode('default_widget')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('theme')
                            ->values(['light', 'dark'])
                            ->defaultValue('light')
                        ->end()
                        ->enumNode('size')
                            ->values(['normal', 'compact', 'invisible'])
                            ->defaultValue('normal')
                        ->end()
                        ->scalarNode('language')
                            ->defaultNull()
                            ->info('ISO 639-1 language code passed as ?hl= on the script include. Null = auto-detect.')
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(static fn (array $v) => ($v['enabled'] ?? true) && empty($v['secret_key']))
                ->thenInvalid('"easy_hcaptcha.secret_key" is required when easy_hcaptcha.enabled is true.')
            ->end()
            ->validate()
                ->ifTrue(static fn (array $v) => ($v['enabled'] ?? true) && empty($v['site_key']))
                ->thenInvalid('"easy_hcaptcha.site_key" is required when easy_hcaptcha.enabled is true.')
            ->end()
        ;

        return $treeBuilder;
    }
}
