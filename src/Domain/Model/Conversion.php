<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Limit;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use DateTimeInterface;

final class Conversion
{
    /** @var Id */
    private $id;

    /** @var Id */
    private $campaignId;

    /** @var Limit */
    private $limit;

    /** @var int */
    private $value;

    /** @var bool */
    private $valueMutable;

    /** @var bool */
    private $repeatable;

    /** @var DateTimeInterface|null */
    private $deletedAt;

    public function __construct(
        Id $id,
        Id $campaignId,
        Limit $limit,
        int $value,
        bool $valueMutable = false,
        bool $repeatable = false
    ) {
        if ($value < 0) {
            throw InvalidArgumentException::fromArgument(
                'value',
                (string)$value,
                'The value must be greater than or equal to 0'
            );
        }

        $this->id = $id;
        $this->campaignId = $campaignId;
        $this->limit = $limit;
        $this->value = $value;
        $this->valueMutable = $valueMutable;
        $this->repeatable = $repeatable;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getCampaignId(): Id
    {
        return $this->campaignId;
    }

    public function getLimit(): Limit
    {
        return $this->limit;
    }

    public function getLimitValue(): ?int
    {
        return $this->limit->getValue();
    }

    public function getLimitType(): LimitType
    {
        return $this->limit->getType();
    }

    public function getCost(): int
    {
        return $this->limit->getCost();
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isValueMutable(): bool
    {
        return $this->valueMutable;
    }

    public function isRepeatable(): bool
    {
        return $this->repeatable;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }
}
