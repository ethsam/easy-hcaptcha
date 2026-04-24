<?php

declare(strict_types=1);

use Ethsam\EasyHcaptcha\Form\Type\HcaptchaType;
use Ethsam\EasyHcaptcha\Twig\HcaptchaExtension;
use Ethsam\EasyHcaptcha\Validator\HcaptchaValidator;
use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifier;
use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire(false)
            ->autoconfigure(false)
            ->public(false);

    $services->set('easy_hcaptcha.verifier', HcaptchaVerifier::class)
        ->args([
            service(HttpClientInterface::class),
            param('easy_hcaptcha.secret_key'),
            param('easy_hcaptcha.verify_url'),
            param('easy_hcaptcha.timeout'),
            param('easy_hcaptcha.enabled'),
            service('logger')->nullOnInvalid(),
        ])
        ->public();

    // Public alias on the interface only — consumers should never depend on
    // the concrete class. The concrete-class alias is private (still
    // resolvable via #[Autowire(service: HcaptchaVerifier::class)] if needed).
    $services->alias(HcaptchaVerifierInterface::class, 'easy_hcaptcha.verifier')->public();
    $services->alias(HcaptchaVerifier::class, 'easy_hcaptcha.verifier');

    $services->set('easy_hcaptcha.twig_extension', HcaptchaExtension::class)
        ->args([
            param('easy_hcaptcha.site_key'),
            param('easy_hcaptcha.default_widget'),
        ])
        ->tag('twig.extension');

    $services->set('easy_hcaptcha.validator', HcaptchaValidator::class)
        ->args([
            service('easy_hcaptcha.verifier'),
            service('request_stack')->nullOnInvalid(),
        ])
        ->tag('validator.constraint_validator');

    $services->set('easy_hcaptcha.form.type', HcaptchaType::class)
        ->args([
            param('easy_hcaptcha.site_key'),
            param('easy_hcaptcha.default_widget'),
        ])
        ->tag('form.type');
};
