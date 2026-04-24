<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Verifier;

use Ethsam\EasyHcaptcha\Exception\HcaptchaApiException;
use Ethsam\EasyHcaptcha\Exception\HcaptchaConfigurationException;
use Ethsam\EasyHcaptcha\Response\HcaptchaResponse;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HcaptchaVerifier implements HcaptchaVerifierInterface
{
    public const DEFAULT_VERIFY_URL = 'https://api.hcaptcha.com/siteverify';

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $secretKey,
        private readonly string $verifyUrl = self::DEFAULT_VERIFY_URL,
        private readonly float $timeout = 5.0,
        private readonly bool $enabled = true,
        ?LoggerInterface $logger = null,
    ) {
        if ($this->enabled && $this->secretKey === '') {
            throw HcaptchaConfigurationException::missingSecretKey();
        }
        if (
            !filter_var($this->verifyUrl, \FILTER_VALIDATE_URL)
            || !str_starts_with($this->verifyUrl, 'https://')
        ) {
            throw HcaptchaConfigurationException::invalidVerifyUrl($this->verifyUrl);
        }

        $this->logger = $logger ?? new NullLogger();
    }

    public function verify(?string $token, ?string $remoteIp = null): HcaptchaResponse
    {
        if (!$this->enabled) {
            return HcaptchaResponse::disabled();
        }

        if ($token === null || $token === '') {
            return HcaptchaResponse::missingToken();
        }

        $body = [
            'secret' => $this->secretKey,
            'response' => $token,
        ];
        if ($remoteIp !== null && $remoteIp !== '') {
            $body['remoteip'] = $remoteIp;
        }

        try {
            $response = $this->client->request('POST', $this->verifyUrl, [
                'body' => $body,
                'timeout' => $this->timeout,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                throw HcaptchaApiException::unexpectedStatus($statusCode);
            }

            // Pass true so a JSON decoding error surfaces as DecodingExceptionInterface
            // instead of being silently stored on the response object.
            $payload = $response->toArray(true);
        } catch (TransportExceptionInterface $e) {
            $this->logger->warning('hCaptcha transport failure: {message}', ['message' => $e->getMessage(), 'exception' => $e]);
            throw HcaptchaApiException::transportFailure($e);
        } catch (HttpExceptionInterface $e) {
            throw HcaptchaApiException::unexpectedStatus($e->getResponse()->getStatusCode());
        } catch (DecodingExceptionInterface $e) {
            throw HcaptchaApiException::malformedJson($e);
        }

        return HcaptchaResponse::fromApiPayload($payload);
    }
}
