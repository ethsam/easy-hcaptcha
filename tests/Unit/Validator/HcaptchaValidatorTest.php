<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Tests\Unit\Validator;

use Ethsam\EasyHcaptcha\Exception\HcaptchaApiException;
use Ethsam\EasyHcaptcha\Response\HcaptchaResponse;
use Ethsam\EasyHcaptcha\Validator\Constraint\Hcaptcha;
use Ethsam\EasyHcaptcha\Validator\HcaptchaValidator;
use Ethsam\EasyHcaptcha\Verifier\HcaptchaVerifierInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class HcaptchaValidatorTest extends ConstraintValidatorTestCase
{
    private InMemoryVerifier $verifier;

    protected function setUp(): void
    {
        $this->verifier = new InMemoryVerifier();
        parent::setUp();
    }

    protected function createValidator(): HcaptchaValidator
    {
        return new HcaptchaValidator($this->verifier, $this->makeRequestStack('203.0.113.4'));
    }

    public function testValidTokenAddsNoViolation(): void
    {
        $this->verifier->next = HcaptchaResponse::fromApiPayload(['success' => true]);
        $this->validator->validate('valid-token', new Hcaptcha());
        $this->assertNoViolation();

        self::assertSame('valid-token', $this->verifier->lastToken);
        self::assertSame('203.0.113.4', $this->verifier->lastIp);
    }

    public function testInvalidTokenAddsInvalidViolation(): void
    {
        $this->verifier->next = HcaptchaResponse::fromApiPayload([
            'success' => false,
            'error-codes' => ['invalid-input-response'],
        ]);
        $constraint = new Hcaptcha(message: 'Bad captcha.');
        $this->validator->validate('bad-token', $constraint);

        $this->buildViolation('Bad captcha.')
            ->setCode('hcaptcha_invalid')
            ->setParameter('{{ error_codes }}', 'invalid-input-response')
            ->assertRaised();
    }

    public function testNullTokenIsTreatedAsInvalid(): void
    {
        $this->verifier->next = HcaptchaResponse::missingToken();
        $this->validator->validate(null, new Hcaptcha());

        $this->buildViolation('The captcha is invalid. Please try again.')
            ->setCode('hcaptcha_invalid')
            ->setParameter('{{ error_codes }}', 'missing-input-response')
            ->assertRaised();
    }

    public function testApiFailureUsesDedicatedMessageAndDoesNotBubbleException(): void
    {
        $this->verifier->throw = HcaptchaApiException::transportFailure(new \RuntimeException('boom'));

        $this->validator->validate('any-token', new Hcaptcha(apiFailureMessage: 'Try again.'));

        $this->buildViolation('Try again.')
            ->setCode('hcaptcha_api_failure')
            ->assertRaised();
    }

    public function testRejectsNonHcaptchaConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('token', new NotNull());
    }

    public function testRejectsNonStringValue(): void
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedValueException::class);
        $this->validator->validate(42, new Hcaptcha());
    }

    public function testFallsBackToHcaptchaResponseRequestParameter(): void
    {
        // Form binding stripped the value (e.g. nested form), but the JS
        // widget injected `h-captcha-response` directly into the request.
        $stack = new RequestStack();
        $stack->push(Request::create('/', 'POST', ['h-captcha-response' => 'fallback-token']));
        $verifier = new InMemoryVerifier();
        $verifier->next = HcaptchaResponse::fromApiPayload(['success' => true]);
        $validator = new HcaptchaValidator($verifier, $stack);
        $validator->initialize($this->context);

        $validator->validate(null, new Hcaptcha());

        self::assertSame('fallback-token', $verifier->lastToken);
        $this->assertNoViolation();
    }

    public function testWorksWithoutRequestStack(): void
    {
        $verifier = new InMemoryVerifier();
        $verifier->next = HcaptchaResponse::fromApiPayload(['success' => true]);
        $validator = new HcaptchaValidator($verifier);
        $validator->initialize($this->context);

        $validator->validate('token', new Hcaptcha());
        $this->assertNoViolation();
        self::assertNull($verifier->lastIp);
    }

    private function makeRequestStack(string $clientIp): RequestStack
    {
        $stack = new RequestStack();
        $request = Request::create('/', 'POST', server: ['REMOTE_ADDR' => $clientIp]);
        $stack->push($request);

        return $stack;
    }
}

final class InMemoryVerifier implements HcaptchaVerifierInterface
{
    public ?HcaptchaResponse $next = null;
    public ?\Throwable $throw = null;
    public ?string $lastToken = null;
    public ?string $lastIp = null;

    public function verify(?string $token, ?string $remoteIp = null): HcaptchaResponse
    {
        $this->lastToken = $token;
        $this->lastIp = $remoteIp;

        if ($this->throw !== null) {
            throw $this->throw;
        }

        return $this->next ?? HcaptchaResponse::disabled();
    }
}
