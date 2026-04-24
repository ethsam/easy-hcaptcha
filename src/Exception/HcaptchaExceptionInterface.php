<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Exception;

/**
 * Marker interface — every exception thrown by this bundle implements it,
 * so callers can `catch (HcaptchaExceptionInterface $e)` to catch them all.
 */
interface HcaptchaExceptionInterface extends \Throwable
{
}
