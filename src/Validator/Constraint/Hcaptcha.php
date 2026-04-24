<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Validator\Constraint;

use Ethsam\EasyHcaptcha\Validator\HcaptchaValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Hcaptcha extends Constraint
{
    public string $message = 'The captcha is invalid. Please try again.';
    public string $apiFailureMessage = 'The captcha could not be verified at the moment. Please try again.';

    /**
     * @param array<string>|string|null $groups
     */
    public function __construct(
        ?string $message = null,
        ?string $apiFailureMessage = null,
        array|string|null $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->apiFailureMessage = $apiFailureMessage ?? $this->apiFailureMessage;
    }

    public function validatedBy(): string
    {
        return HcaptchaValidator::class;
    }
}
