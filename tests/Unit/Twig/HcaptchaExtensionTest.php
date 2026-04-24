<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Tests\Unit\Twig;

use Ethsam\EasyHcaptcha\Twig\HcaptchaExtension;
use PHPUnit\Framework\TestCase;

final class HcaptchaExtensionTest extends TestCase
{
    public function testRenderScriptWithoutOptions(): void
    {
        $ext = new HcaptchaExtension('site-key');
        self::assertSame(
            '<script src="https://js.hcaptcha.com/1/api.js" async defer></script>',
            $ext->renderScript(),
        );
    }

    public function testRenderScriptAppendsLanguageFromOptions(): void
    {
        $ext = new HcaptchaExtension('site-key');
        $html = $ext->renderScript(['hl' => 'fr']);
        self::assertStringContainsString('hl=fr', $html);
    }

    public function testRenderScriptUsesDefaultLanguageWhenNoOption(): void
    {
        $ext = new HcaptchaExtension('site-key', ['language' => 'es']);
        $html = $ext->renderScript();
        self::assertStringContainsString('hl=es', $html);
    }

    public function testRenderScriptSupportsRecaptchacompat(): void
    {
        $ext = new HcaptchaExtension('site-key');
        $html = $ext->renderScript(['recaptchacompat' => true]);
        self::assertStringContainsString('recaptchacompat=on', $html);
    }

    public function testRenderWidgetIncludesSitekey(): void
    {
        $ext = new HcaptchaExtension('my-key');
        $html = $ext->renderWidget();
        self::assertStringContainsString('data-sitekey="my-key"', $html);
        self::assertStringContainsString('class="h-captcha"', $html);
    }

    public function testRenderWidgetHonoursDefaults(): void
    {
        $ext = new HcaptchaExtension('my-key', ['theme' => 'dark', 'size' => 'compact']);
        $html = $ext->renderWidget();
        self::assertStringContainsString('data-theme="dark"', $html);
        self::assertStringContainsString('data-size="compact"', $html);
    }

    public function testRenderWidgetOptionsOverrideDefaults(): void
    {
        $ext = new HcaptchaExtension('my-key', ['theme' => 'dark']);
        $html = $ext->renderWidget(['theme' => 'light']);
        self::assertStringContainsString('data-theme="light"', $html);
        self::assertStringNotContainsString('data-theme="dark"', $html);
    }

    public function testRenderWidgetEscapesAttributes(): void
    {
        $ext = new HcaptchaExtension('"><script>alert(1)</script>');
        $html = $ext->renderWidget();
        self::assertStringNotContainsString('<script>alert(1)', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testRenderInvisibleWidgetForcesInvisibleSize(): void
    {
        $ext = new HcaptchaExtension('my-key', ['size' => 'normal']);
        $html = $ext->renderInvisibleWidget();
        self::assertStringContainsString('data-size="invisible"', $html);
    }

    public function testRenderWidgetMergesUserClass(): void
    {
        $ext = new HcaptchaExtension('my-key');
        $html = $ext->renderWidget(['class' => 'mt-4']);
        self::assertStringContainsString('class="h-captcha mt-4"', $html);
    }

    public function testRenderWidgetRejectsMaliciousAttributeNames(): void
    {
        $ext = new HcaptchaExtension('my-key');
        $html = $ext->renderWidget(['data-x" onerror=alert(1) x="' => 'y']);

        self::assertStringNotContainsString('onerror', $html);
        self::assertStringNotContainsString('alert', $html);
    }

    public function testRenderScriptStripsMaliciousOnloadCallback(): void
    {
        $ext = new HcaptchaExtension('site-key');
        $html = $ext->renderScript(['onload' => 'alert(1);//']);

        self::assertStringNotContainsString('alert', $html);
        self::assertStringNotContainsString('onload=', $html);
    }

    public function testRenderScriptKeepsValidOnloadCallback(): void
    {
        $ext = new HcaptchaExtension('site-key');
        $html = $ext->renderScript(['onload' => 'myCallback']);

        self::assertStringContainsString('onload=myCallback', $html);
    }

    public function testHcaptchaSiteKeyFunctionExposesKey(): void
    {
        $ext = new HcaptchaExtension('exposed-key');
        $functions = [];
        foreach ($ext->getFunctions() as $function) {
            $functions[$function->getName()] = $function->getCallable();
        }

        self::assertArrayHasKey('hcaptcha_site_key', $functions);
        self::assertSame('exposed-key', $functions['hcaptcha_site_key']());
    }
}
