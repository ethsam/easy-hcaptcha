<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Tests\Unit\Response;

use Ethsam\EasyHcaptcha\Response\HcaptchaResponse;
use PHPUnit\Framework\TestCase;

final class HcaptchaResponseTest extends TestCase
{
    public function testFromApiPayloadParsesAllFields(): void
    {
        $response = HcaptchaResponse::fromApiPayload([
            'success' => true,
            'challenge_ts' => '2026-04-24T10:00:00Z',
            'hostname' => 'example.com',
            'credit' => true,
            'error-codes' => ['some-warning'],
            'score' => 0.42,
            'score_reason' => ['automation', 'malformed-input'],
        ]);

        self::assertTrue($response->isSuccess());
        self::assertSame('example.com', $response->hostname);
        self::assertSame('2026-04-24T10:00:00Z', $response->challengeTs);
        self::assertTrue($response->credit);
        self::assertSame(['some-warning'], $response->errorCodes);
        self::assertSame(0.42, $response->score);
        self::assertSame(['automation', 'malformed-input'], $response->scoreReason);
    }

    public function testFromApiPayloadWithMinimalData(): void
    {
        $response = HcaptchaResponse::fromApiPayload(['success' => false]);

        self::assertFalse($response->isSuccess());
        self::assertSame([], $response->errorCodes);
        self::assertNull($response->hostname);
        self::assertNull($response->score);
    }

    public function testFromApiPayloadDefaultsSuccessToFalseWhenMissing(): void
    {
        $response = HcaptchaResponse::fromApiPayload([]);

        self::assertFalse($response->isSuccess());
    }

    public function testFromApiPayloadDefendsAgainstNonArrayErrorCodes(): void
    {
        $response = HcaptchaResponse::fromApiPayload([
            'success' => false,
            'error-codes' => 'invalid-input-response',
        ]);

        self::assertSame([], $response->errorCodes);
    }

    public function testFromApiPayloadDefendsAgainstNonArrayScoreReason(): void
    {
        $response = HcaptchaResponse::fromApiPayload([
            'success' => true,
            'score' => 0.5,
            'score_reason' => 'automation',
        ]);

        self::assertNull($response->scoreReason);
    }

    public function testHasErrorCodeChecksMembership(): void
    {
        $response = HcaptchaResponse::fromApiPayload([
            'success' => false,
            'error-codes' => ['missing-input-response', 'invalid-input-response'],
        ]);

        self::assertTrue($response->hasErrorCode('missing-input-response'));
        self::assertFalse($response->hasErrorCode('bad-request'));
    }

    public function testDisabledIsAlwaysSuccess(): void
    {
        $response = HcaptchaResponse::disabled();

        self::assertTrue($response->isSuccess());
        self::assertSame([], $response->errorCodes);
    }

    public function testMissingTokenCarriesOfficialErrorCode(): void
    {
        $response = HcaptchaResponse::missingToken();

        self::assertFalse($response->isSuccess());
        self::assertSame(['missing-input-response'], $response->errorCodes);
    }

    public function testStringableYieldsTruthyOnSuccessAndFalsyOnFailure(): void
    {
        self::assertNotEmpty((string) HcaptchaResponse::disabled());
        self::assertSame('', (string) HcaptchaResponse::missingToken());

        // Native PHP truthiness must hold for legacy `if ($response)` callers
        self::assertTrue((bool) (string) HcaptchaResponse::disabled());
        self::assertFalse((bool) (string) HcaptchaResponse::missingToken());
    }

    public function testJsonSerializeRoundTripsToArray(): void
    {
        $response = HcaptchaResponse::fromApiPayload([
            'success' => true,
            'hostname' => 'example.com',
        ]);

        $json = json_encode($response, \JSON_THROW_ON_ERROR);
        self::assertJson($json);
        $decoded = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame(true, $decoded['success']);
        self::assertSame('example.com', $decoded['hostname']);
    }

    public function testToArrayDropsNullsButKeepsFalsyValues(): void
    {
        $response = HcaptchaResponse::fromApiPayload(['success' => true]);
        $array = $response->toArray();

        self::assertSame(true, $array['success']);
        self::assertArrayHasKey('error-codes', $array);
        self::assertSame([], $array['error-codes']);
        self::assertArrayNotHasKey('hostname', $array);
        self::assertArrayNotHasKey('score', $array);
    }

    public function testToArrayKeepsZeroScoreAndFalseCredit(): void
    {
        $response = HcaptchaResponse::fromApiPayload([
            'success' => false,
            'score' => 0.0,
            'credit' => false,
        ]);
        $array = $response->toArray();

        self::assertSame(0.0, $array['score']);
        self::assertSame(false, $array['credit']);
    }
}
