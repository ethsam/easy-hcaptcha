<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Exception;

final class HcaptchaApiException extends HcaptchaException
{
    public static function transportFailure(\Throwable $previous): self
    {
        return new self('Unable to reach the hCaptcha verification API.', 0, $previous);
    }

    public static function unexpectedStatus(int $status): self
    {
        return new self(\sprintf('hCaptcha API returned unexpected HTTP status %d.', $status));
    }

    public static function malformedJson(\Throwable $previous): self
    {
        return new self('hCaptcha API returned a malformed JSON payload.', 0, $previous);
    }
}
