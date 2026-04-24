<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Verifier;

use Ethsam\EasyHcaptcha\Exception\HcaptchaApiException;
use Ethsam\EasyHcaptcha\Response\HcaptchaResponse;

interface HcaptchaVerifierInterface
{
    /**
     * Validate a hCaptcha token against the siteverify API.
     *
     * @param string|null $token    The "h-captcha-response" value posted by the form
     * @param string|null $remoteIp The end-user IP, optional but recommended for fraud scoring
     *
     * @throws HcaptchaApiException On transport errors, non-2xx responses or malformed JSON
     */
    public function verify(?string $token, ?string $remoteIp = null): HcaptchaResponse;
}
