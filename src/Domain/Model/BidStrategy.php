<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\ValueObject\Id;
use DateTimeInterface;

final class BidStrategy
{
    private const CATEGORY_LENGTH_MAXIMUM = 267;

    /** @var Id */
    private $id;

    /** @var string */
    private $category;

    /** @var float */
    private $rank;

    /** @var DateTimeInterface|null */
    private $deletedAt;

    public function __construct(Id $id, string $category, float $rank, DateTimeInterface $deletedAt = null)
    {
        if (strlen($category) > self::CATEGORY_LENGTH_MAXIMUM) {
            throw InvalidArgumentException::fromArgument(
                'category',
                $category,
                sprintf("The value's maximal length is limited to %d chars", self::CATEGORY_LENGTH_MAXIMUM)
            );
        }

        if ($rank < 0) {
            throw InvalidArgumentException::fromArgument(
                'rank',
                (string)$rank,
                'The value cannot be negative'
            );
        }

        $this->id = $id;
        $this->category = $category;
        $this->rank = $rank;
        $this->deletedAt = $deletedAt;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getRank(): float
    {
        return $this->rank;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }
}
