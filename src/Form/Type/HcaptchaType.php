<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Form\Type;

use Ethsam\EasyHcaptcha\Validator\Constraint\Hcaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HcaptchaType extends AbstractType
{
    /**
     * @param array{theme?: string, size?: string, language?: string|null} $defaultWidget
     */
    public function __construct(
        private readonly string $siteKey,
        private readonly array $defaultWidget = [],
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'mapped' => false,
                'required' => false,
                'error_bubbling' => false,
                'compound' => false,
                'theme' => $this->defaultWidget['theme'] ?? 'light',
                'size' => $this->defaultWidget['size'] ?? 'normal',
                'language' => $this->defaultWidget['language'] ?? null,
                'invisible' => false,
                'site_key' => $this->siteKey,
                'constraints' => [new Hcaptcha()],
            ])
            ->setAllowedTypes('theme', ['string'])
            ->setAllowedTypes('size', ['string'])
            ->setAllowedTypes('language', ['null', 'string'])
            ->setAllowedTypes('invisible', ['bool'])
            ->setAllowedTypes('site_key', ['string'])
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['site_key'] = $options['site_key'];
        $view->vars['theme'] = $options['theme'];
        $view->vars['size'] = $options['invisible'] ? 'invisible' : $options['size'];
        $view->vars['language'] = $options['language'];
        $view->vars['invisible'] = $options['invisible'];
        // hCaptcha JS injects an <input name="h-captcha-response"> into the
        // surrounding <form>; we expose it as a view var so themes can render
        // a hidden input fallback if needed, but we never touch full_name to
        // preserve Symfony's parent-prefixed naming for nested forms.
        $view->vars['response_field'] = 'h-captcha-response';
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'hcaptcha';
    }
}
