<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\DependencyInjection;

use Ethsam\EasyHcaptcha\Exception\HcaptchaConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class EasyHcaptchaExtension extends ConfigurableExtension
{
    public function getAlias(): string
    {
        return 'easy_hcaptcha';
    }

    /**
     * @param array<string, mixed> $mergedConfig
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/config'));
        $loader->load('services.php');

        $container->setParameter('easy_hcaptcha.site_key', $mergedConfig['site_key'] ?? '');
        $container->setParameter('easy_hcaptcha.secret_key', $mergedConfig['secret_key'] ?? '');
        $container->setParameter('easy_hcaptcha.verify_url', $mergedConfig['verify_url']);
        $container->setParameter('easy_hcaptcha.timeout', (float) $mergedConfig['timeout']);
        $container->setParameter('easy_hcaptcha.enabled', (bool) $mergedConfig['enabled']);
        $container->setParameter('easy_hcaptcha.default_widget', $mergedConfig['default_widget']);

        if (!empty($mergedConfig['http_client'])) {
            $clientId = (string) $mergedConfig['http_client'];
            if (!$container->hasDefinition($clientId) && !$container->hasAlias($clientId)) {
                throw new HcaptchaConfigurationException(\sprintf(
                    'easy_hcaptcha.http_client refers to service "%s" which is not registered in the container.',
                    $clientId,
                ));
            }
            $container->getDefinition('easy_hcaptcha.verifier')
                ->replaceArgument(0, new Reference($clientId));
        }
    }
}
