<?php

declare(strict_types=1);

namespace Ethsam\EasyHcaptcha\Response;

/**
 * Immutable result of a call to the hCaptcha siteverify endpoint.
 *
 * Implements Stringable so legacy `if ($verifier->verify($token))` calls keep
 * working when migrating from the v1 API which returned a raw bool.
 */
final class HcaptchaResponse implements \Stringable, \JsonSerializable
{
    /**
     * @param list<string>      $errorCodes  Codes returned in the "error-codes" array
     * @param list<string>|null $scoreReason Enterprise-only "score_reason" field
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $errorCodes = [],
        public readonly ?string $hostname = null,
        public readonly ?string $challengeTs = null,
        public readonly ?float $score = null,
        public readonly ?array $scoreReason = null,
        public readonly ?bool $credit = null,
    ) {
    }

    /**
     * Build a response from the raw decoded JSON payload returned by the API.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromApiPayload(array $payload): self
    {
        $errorCodes = $payload['error-codes'] ?? [];
        if (!\is_array($errorCodes)) {
            $errorCodes = [];
        }

        $scoreReason = $payload['score_reason'] ?? null;
        if ($scoreReason !== null && !\is_array($scoreReason)) {
            $scoreReason = null;
        }

        return new self(
            success: (bool) ($payload['success'] ?? false),
            errorCodes: array_values(array_map('strval', $errorCodes)),
            hostname: isset($payload['hostname']) ? (string) $payload['hostname'] : null,
            challengeTs: isset($payload['challenge_ts']) ? (string) $payload['challenge_ts'] : null,
            score: isset($payload['score']) ? (float) $payload['score'] : null,
            scoreReason: $scoreReason !== null ? array_values(array_map('strval', $scoreReason)) : null,
            credit: isset($payload['credit']) ? (bool) $payload['credit'] : null,
        );
    }

    /**
     * Synthetic always-success response used when the bundle is disabled
     * (typically in the test environment) so flows do not require a network call.
     */
    public static function disabled(): self
    {
        return new self(success: true);
    }

    /**
     * Synthetic failure response used when no token was supplied at all,
     * mirroring the official "missing-input-response" error code so callers
     * can treat the case uniformly without an HTTP call.
     */
    public static function missingToken(): self
    {
        return new self(success: false, errorCodes: ['missing-input-response']);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return list<string>
     */
    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }

    public function hasErrorCode(string $code): bool
    {
        return \in_array($code, $this->errorCodes, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = [
            'success' => $this->success,
            'error-codes' => $this->errorCodes,
            'hostname' => $this->hostname,
            'challenge_ts' => $this->challengeTs,
            'score' => $this->score,
            'score_reason' => $this->scoreReason,
            'credit' => $this->credit,
        ];

        // Drop null-only keys; keep falsy-but-meaningful values like false/0.0.
        return array_filter($array, static fn ($value) => $value !== null);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Casts to a non-empty string on success and an empty string on failure,
     * keeping `if ($verifier->verify(...))` truthy/falsy as in v1.
     */
    public function __toString(): string
    {
        return $this->success ? '1' : '';
    }
}
