<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Validator;

use Ethsam\EasyHcaptcha\Exception\HcaptchaApiException;
use Ethsam\EasyHcaptcha\Validator\Constraint\Hcaptcha;
use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class HcaptchaValidator extends ConstraintValidator
{
    public function __construct(
        private readonly HcaptchaVerifierInterface $verifier,
        private readonly ?RequestStack $requestStack = null,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Hcaptcha) {
            throw new UnexpectedTypeException($constraint, Hcaptcha::class);
        }

        if ($value !== null && !\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $request = $this->requestStack?->getCurrentRequest();

        // The hCaptcha JS widget injects an <input name="h-captcha-response">
        // directly into the form element on the page; the value never lands in
        // a Symfony Form field that has a parent prefix. When the constraint
        // is attached to such a field (or to a DTO populated from form data),
        // the bound value is empty — fall back to the raw request parameter.
        if (($value === null || $value === '') && $request !== null) {
            $value = $request->request->get('h-captcha-response');
            if (!\is_string($value)) {
                $value = null;
            }
        }

        try {
            $response = $this->verifier->verify($value, $request?->getClientIp());
        } catch (HcaptchaApiException) {
            $this->context->buildViolation($constraint->apiFailureMessage)
                ->setCode('hcaptcha_api_failure')
                ->addViolation();

            return;
        }

        if (!$response->isSuccess()) {
            $this->context->buildViolation($constraint->message)
                ->setCode('hcaptcha_invalid')
                ->setParameter('{{ error_codes }}', implode(', ', $response->getErrorCodes()))
                ->addViolation();
        }
    }
}
