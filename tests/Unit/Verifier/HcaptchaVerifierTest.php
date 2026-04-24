<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Tests\Unit\Verifier;

use Ethsam\EasyHcaptcha\Exception\HcaptchaApiException;
use Ethsam\EasyHcaptcha\Exception\HcaptchaConfigurationException;
use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class HcaptchaVerifierTest extends TestCase
{
    public function testReturnsSuccessResponseOnHappyPath(): void
    {
        $verifier = $this->makeVerifier(static function (string $method, string $url, array $options): MockResponse {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertStringContainsString('secret=secret', $options['body']);
            self::assertStringContainsString('response=token', $options['body']);

            return new MockResponse(json_encode([
                'success' => true,
                'hostname' => 'example.com',
                'challenge_ts' => '2026-04-24T10:00:00Z',
            ], \JSON_THROW_ON_ERROR));
        });

        $response = $verifier->verify('token');

        self::assertTrue($response->isSuccess());
        self::assertSame('example.com', $response->hostname);
    }

    public function testForwardsRemoteIpWhenProvided(): void
    {
        $verifier = $this->makeVerifier(static function (string $method, string $url, array $options): MockResponse {
            self::assertStringContainsString('remoteip=203.0.113.4', $options['body']);

            return new MockResponse('{"success":true}');
        });

        $verifier->verify('token', '203.0.113.4');
    }

    public function testReturnsMissingTokenResponseWithoutHttpCallWhenTokenIsNull(): void
    {
        $client = new MockHttpClient(static function () {
            self::fail('No HTTP call should be made when token is null.');
        });

        $verifier = new HcaptchaVerifier($client, 'secret');
        $response = $verifier->verify(null);

        self::assertFalse($response->isSuccess());
        self::assertSame(['missing-input-response'], $response->errorCodes);
    }

    public function testReturnsMissingTokenResponseWithoutHttpCallWhenTokenIsEmpty(): void
    {
        $client = new MockHttpClient(static function () {
            self::fail('No HTTP call should be made when token is empty.');
        });

        $verifier = new HcaptchaVerifier($client, 'secret');
        $response = $verifier->verify('');

        self::assertFalse($response->isSuccess());
    }

    public function testDisabledVerifierShortCircuitsToSuccessWithoutHttpCall(): void
    {
        $client = new MockHttpClient(static function () {
            self::fail('No HTTP call should be made when verifier is disabled.');
        });

        $verifier = new HcaptchaVerifier($client, '', enabled: false);
        $response = $verifier->verify('whatever');

        self::assertTrue($response->isSuccess());
    }

    public function testThrowsApiExceptionOnNon2xxResponse(): void
    {
        $verifier = $this->makeVerifier(static fn () => new MockResponse('Server Error', ['http_code' => 500]));

        $this->expectException(HcaptchaApiException::class);
        $this->expectExceptionMessage('500');

        $verifier->verify('token');
    }

    public function testThrowsApiExceptionOnTransportFailure(): void
    {
        $client = new MockHttpClient(static function () {
            throw new TransportException('connection refused');
        });

        $verifier = new HcaptchaVerifier($client, 'secret');

        $this->expectException(HcaptchaApiException::class);
        $verifier->verify('token');
    }

    public function testThrowsApiExceptionOnMalformedJson(): void
    {
        $verifier = $this->makeVerifier(static fn () => new MockResponse('this is not json'));

        $this->expectException(HcaptchaApiException::class);
        $verifier->verify('token');
    }

    public function testThrowsConfigurationExceptionWhenSecretMissingAndEnabled(): void
    {
        $this->expectException(HcaptchaConfigurationException::class);
        new HcaptchaVerifier(new MockHttpClient(), '');
    }

    public function testThrowsConfigurationExceptionOnInvalidVerifyUrl(): void
    {
        $this->expectException(HcaptchaConfigurationException::class);
        new HcaptchaVerifier(new MockHttpClient(), 'secret', verifyUrl: 'not-a-url');
    }

    public function testAllowsEmptySecretWhenDisabled(): void
    {
        $verifier = new HcaptchaVerifier(new MockHttpClient(), '', enabled: false);
        self::assertTrue($verifier->verify('any')->isSuccess());
    }

    public function testRejectsNonHttpsVerifyUrl(): void
    {
        $this->expectException(HcaptchaConfigurationException::class);
        new HcaptchaVerifier(new MockHttpClient(), 'secret', verifyUrl: 'http://api.hcaptcha.com/siteverify');
    }

    public function testEmptyRemoteIpIsNotForwarded(): void
    {
        $verifier = $this->makeVerifier(static function (string $method, string $url, array $options): MockResponse {
            self::assertStringNotContainsString('remoteip=', $options['body']);

            return new MockResponse('{"success":true}');
        });

        $verifier->verify('token', '');
    }

    public function testPropagatesApiFailureWithErrorCodes(): void
    {
        $verifier = $this->makeVerifier(static fn () => new MockResponse(json_encode([
            'success' => false,
            'error-codes' => ['invalid-input-response', 'sitekey-secret-mismatch'],
        ], \JSON_THROW_ON_ERROR)));

        $response = $verifier->verify('token');

        self::assertFalse($response->isSuccess());
        self::assertSame(['invalid-input-response', 'sitekey-secret-mismatch'], $response->errorCodes);
    }

    public function testAccepts204AsValidStatus(): void
    {
        $verifier = $this->makeVerifier(static fn () => new MockResponse('', ['http_code' => 204]));

        $this->expectException(HcaptchaApiException::class);
        // 204 has no body, so JSON decoding will fail; we still treat 2xx as
        // a successful HTTP exchange and let the JSON layer surface the issue.
        $verifier->verify('token');
    }

    private function makeVerifier(callable $responseFactory): HcaptchaVerifier
    {
        return new HcaptchaVerifier(new MockHttpClient($responseFactory), 'secret');
    }
}
