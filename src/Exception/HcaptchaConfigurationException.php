<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Exception;

final class HcaptchaConfigurationException extends HcaptchaException
{
    public static function missingSecretKey(): self
    {
        return new self('The hCaptcha secret key is not configured. Set easy_hcaptcha.secret_key (or HCAPTCHA_SECRET_KEY env var).');
    }

    public static function invalidVerifyUrl(string $url): self
    {
        return new self(\sprintf('The hCaptcha verify_url "%s" is not a valid URL.', $url));
    }
}
