<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;

final class PaymentStatus
{
    public const ACCEPTED = 0;

    public const CAMPAIGN_NOT_FOUND = 1;

    public const HUMAN_SCORE_TOO_LOW = 2;

    public const INVALID_TARGETING = 3;

    public const BANNER_NOT_FOUND = 4;

    public const CAMPAIGN_OUTDATED = 5;

    private static $labels = [
        self::ACCEPTED => 'accepted',
        self::CAMPAIGN_NOT_FOUND => 'rejected:campaign_not_found',
        self::HUMAN_SCORE_TOO_LOW => 'rejected:human_score_too_low',
        self::INVALID_TARGETING => 'rejected:invalid_targeting',
        self::BANNER_NOT_FOUND => 'rejected:banner_not_found',
        self::CAMPAIGN_OUTDATED => 'rejected:campaign_outdated',
    ];

    /** @var ?int */
    private $status;

    public function __construct(?int $status = null)
    {
        if ($status !== null && $status < 0) {
            throw InvalidArgumentException::fromArgument('status', (string)$status);
        }

        $this->status = $status;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function isProcessed(): bool
    {
        return $this->status !== null;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->isProcessed() && !$this->isAccepted();
    }

    public function toString(): string
    {
        if (!$this->isProcessed()) {
            return 'unprocessed';
        }
        if (array_key_exists($this->status, self::$labels)) {
            return self::$labels[$this->status];
        }

        return 'rejected:unknown';
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
