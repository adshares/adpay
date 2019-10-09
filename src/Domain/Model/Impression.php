<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\Id;

final class Impression
{
    /** @var Id */
    private $id;

    /** @var Id */
    private $trackingId;

    /** @var Id */
    private $userId;

    /** @var Context */
    private $context;

    /** @var float */
    private $humanScore;

    public function __construct(Id $id, Id $trackingId, Id $userId, Context $context, float $humanScore)
    {
        if ($humanScore < 0 || $humanScore > 1) {
            throw InvalidArgumentException::fromArgument(
                'human score',
                (string)$humanScore,
                'Must be in the range of <0, 1>.'
            );
        }

        $this->id = $id;
        $this->trackingId = $trackingId;
        $this->userId = $userId;
        $this->context = $context;
        $this->humanScore = $humanScore;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getTrackingId(): Id
    {
        return $this->trackingId;
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getContextData(): array
    {
        return $this->context->all();
    }

    public function getHumanScore(): float
    {
        return $this->humanScore;
    }
}
