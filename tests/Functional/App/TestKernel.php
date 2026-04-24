<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Tests\Functional\App;

use Ethsam\EasyHcaptcha\EasyHcaptchaBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @var array<string, mixed>
     */
    private array $bundleConfig = [];

    public function __construct(string $environment = 'test', bool $debug = true)
    {
        parent::__construct($environment, $debug);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function withBundleConfig(array $config): self
    {
        $this->bundleConfig = $config;

        return $this;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new EasyHcaptchaBundle(),
        ];
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/easy-hcaptcha-tests/'.spl_object_hash($this).'/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/easy-hcaptcha-tests/'.spl_object_hash($this).'/logs';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => true],
            'router' => ['utf8' => true],
            'http_client' => null,
        ]);

        $container->extension('twig', [
            'default_path' => __DIR__.'/templates',
            'strict_variables' => true,
        ]);

        $container->extension('easy_hcaptcha', $this->bundleConfig + [
            'site_key' => 'test-site-key',
            'secret_key' => 'test-secret-key',
            'enabled' => false,
        ]);
    }
}
