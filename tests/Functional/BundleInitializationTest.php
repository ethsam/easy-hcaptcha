<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Tests\Functional;

use Ethsam\EasyHcaptcha\Form\Type\HcaptchaType;
use Ethsam\EasyHcaptcha\Tests\Functional\App\TestKernel;
use Ethsam\EasyHcaptcha\Twig\HcaptchaExtension;
use Ethsam\EasyHcaptcha\Validator\HcaptchaValidator;
use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifier;
use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testKernelBootsAndExposesPublicServices(): void
    {
        $container = self::getContainer();

        self::assertTrue($container->has('easy_hcaptcha.verifier'));
        self::assertInstanceOf(HcaptchaVerifier::class, $container->get('easy_hcaptcha.verifier'));
        self::assertInstanceOf(HcaptchaVerifier::class, $container->get(HcaptchaVerifierInterface::class));
        self::assertInstanceOf(HcaptchaValidator::class, $container->get('easy_hcaptcha.validator'));
        self::assertInstanceOf(HcaptchaExtension::class, $container->get('easy_hcaptcha.twig_extension'));
        self::assertInstanceOf(HcaptchaType::class, $container->get('easy_hcaptcha.form.type'));
    }

    public function testParametersAreSetFromConfig(): void
    {
        $container = self::getContainer();

        self::assertSame('test-site-key', $container->getParameter('easy_hcaptcha.site_key'));
        self::assertSame('test-secret-key', $container->getParameter('easy_hcaptcha.secret_key'));
        self::assertFalse($container->getParameter('easy_hcaptcha.enabled'));
    }

    public function testDisabledVerifierShortCircuits(): void
    {
        /** @var HcaptchaVerifierInterface $verifier */
        $verifier = self::getContainer()->get(HcaptchaVerifierInterface::class);

        $response = $verifier->verify('any-token');
        self::assertTrue($response->isSuccess());
    }
}
