<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Tests\Unit\DependencyInjection;

use Ethsam\EasyHcaptcha\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testFullConfigurationIsAccepted(): void
    {
        $config = $this->process([[
            'site_key' => 'pub-key',
            'secret_key' => 'sec-key',
            'verify_url' => 'https://example.com/verify',
            'timeout' => 3.5,
            'enabled' => true,
            'default_widget' => ['theme' => 'dark', 'size' => 'compact', 'language' => 'fr'],
        ]]);

        self::assertSame('pub-key', $config['site_key']);
        self::assertSame('sec-key', $config['secret_key']);
        self::assertSame('https://example.com/verify', $config['verify_url']);
        self::assertSame(3.5, $config['timeout']);
        self::assertTrue($config['enabled']);
        self::assertSame('dark', $config['default_widget']['theme']);
    }

    public function testDefaultsAreApplied(): void
    {
        $config = $this->process([[
            'site_key' => 'pub-key',
            'secret_key' => 'sec-key',
        ]]);

        self::assertSame('https://api.hcaptcha.com/siteverify', $config['verify_url']);
        self::assertSame(5.0, $config['timeout']);
        self::assertTrue($config['enabled']);
        self::assertSame('light', $config['default_widget']['theme']);
        self::assertSame('normal', $config['default_widget']['size']);
        self::assertNull($config['default_widget']['language']);
    }

    public function testSecretKeyIsRequiredWhenEnabled(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('secret_key');
        $this->process([['site_key' => 'pub']]);
    }

    public function testSiteKeyIsRequiredWhenEnabled(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('site_key');
        $this->process([['secret_key' => 'sec']]);
    }

    public function testKeysAreOptionalWhenDisabled(): void
    {
        $config = $this->process([[
            'enabled' => false,
        ]]);

        self::assertFalse($config['enabled']);
        self::assertNull($config['site_key']);
        self::assertNull($config['secret_key']);
    }

    public function testInvalidVerifyUrlIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process([[
            'site_key' => 'pub',
            'secret_key' => 'sec',
            'verify_url' => 'not-a-url',
        ]]);
    }

    public function testRejectsUnknownTheme(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process([[
            'site_key' => 'pub',
            'secret_key' => 'sec',
            'default_widget' => ['theme' => 'neon'],
        ]]);
    }

    public function testRejectsTimeoutBelowMinimum(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process([[
            'site_key' => 'pub',
            'secret_key' => 'sec',
            'timeout' => 0.0,
        ]]);
    }

    public function testRejectsUnknownWidgetSize(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process([[
            'site_key' => 'pub',
            'secret_key' => 'sec',
            'default_widget' => ['size' => 'gigantic'],
        ]]);
    }

    public function testHttpClientServiceIdAccepted(): void
    {
        $config = $this->process([[
            'site_key' => 'pub',
            'secret_key' => 'sec',
            'http_client' => 'app.my_http_client',
        ]]);

        self::assertSame('app.my_http_client', $config['http_client']);
    }

    /**
     * @param array<int, array<string, mixed>> $configs
     *
     * @return array<string, mixed>
     */
    private function process(array $configs): array
    {
        return (new Processor())->processConfiguration(new Configuration(), $configs);
    }
}
