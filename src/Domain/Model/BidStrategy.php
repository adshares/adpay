<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Id;

final class BidStrategy
{
    private const CATEGORY_LENGTH_MAXIMUM = 267;

    /** @var Id */
    private $id;

    /** @var string */
    private $category;

    /** @var float */
    private $rank;

    public function __construct(Id $id, string $category, float $rank)
    {
        if (strlen($category) > self::CATEGORY_LENGTH_MAXIMUM) {
            throw InvalidArgumentException::fromArgument(
                'category',
                $category,
                sprintf("The value's maximal length is limited to %d chars", self::CATEGORY_LENGTH_MAXIMUM)
            );
        }

        if ($rank < 0 || $rank > 1) {
            throw InvalidArgumentException::fromArgument(
                'rank',
                (string)$rank,
                'The value must be in range <0, 1>'
            );
        }

        $this->id = $id;
        $this->category = $category;
        $this->rank = $rank;
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
}
