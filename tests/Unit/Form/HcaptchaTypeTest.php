<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Tests\Unit\Form;

use Ethsam\EasyHcaptcha\Form\Type\HcaptchaType;
use Ethsam\EasyHcaptcha\Validator\Constraint\Hcaptcha;
use Symfony\Component\Form\Test\TypeTestCase;

final class HcaptchaTypeTest extends TypeTestCase
{
    public function testViewExposesSitekeyAndDefaults(): void
    {
        $form = $this->factory->create(HcaptchaType::class, null, [
            'site_key' => 'pub-key',
        ]);
        $view = $form->createView();

        self::assertSame('pub-key', $view->vars['site_key']);
        self::assertSame('light', $view->vars['theme']);
        self::assertSame('normal', $view->vars['size']);
        self::assertSame('h-captcha-response', $view->vars['response_field']);
        self::assertFalse($view->vars['invisible']);
    }

    public function testInvisibleOptionForcesInvisibleSize(): void
    {
        $form = $this->factory->create(HcaptchaType::class, null, [
            'invisible' => true,
            'size' => 'normal',
        ]);
        $view = $form->createView();

        self::assertSame('invisible', $view->vars['size']);
        self::assertTrue($view->vars['invisible']);
    }

    public function testCustomThemeAndLanguage(): void
    {
        $form = $this->factory->create(HcaptchaType::class, null, [
            'theme' => 'dark',
            'language' => 'fr',
        ]);
        $view = $form->createView();

        self::assertSame('dark', $view->vars['theme']);
        self::assertSame('fr', $view->vars['language']);
    }

    public function testHcaptchaConstraintIsAttachedByDefault(): void
    {
        $form = $this->factory->create(HcaptchaType::class);
        $constraints = $form->getConfig()->getOption('constraints');

        self::assertCount(1, $constraints);
        self::assertInstanceOf(Hcaptcha::class, $constraints[0]);
    }

    public function testCallerCanOverrideConstraints(): void
    {
        $form = $this->factory->create(HcaptchaType::class, null, [
            'constraints' => [],
        ]);

        self::assertSame([], $form->getConfig()->getOption('constraints'));
    }

    /**
     * @return array<int, \Symfony\Component\Form\FormTypeExtensionInterface>
     */
    protected function getTypes(): array
    {
        return [new HcaptchaType('default-site-key', ['theme' => 'light', 'size' => 'normal', 'language' => null])];
    }

    /**
     * Symfony >= 5.4 expects this signature; we override getTypes() above for FormFactory registration.
     */
    protected function getExtensions(): array
    {
        return [
            new \Symfony\Component\Form\PreloadedExtension($this->getTypes(), []),
        ];
    }
}
