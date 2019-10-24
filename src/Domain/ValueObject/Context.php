<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;

class Context
{
    /** @var float */
    private $humanScore;

    /** @var float */
    private $pageRank;

    /** @var array */
    private $keywords;

    /* @var array */
    private $data;

    public function __construct(float $humanScore, float $pageRank, array $keywords = [], array $data = [])
    {
        if ($humanScore < 0 || $humanScore > 1) {
            throw InvalidArgumentException::fromArgument(
                'human score',
                (string)$humanScore,
                'Must be in the range of <0, 1>.'
            );
        }
        if ($pageRank < 0 || $pageRank > 1) {
            throw InvalidArgumentException::fromArgument(
                'page rank',
                (string)$pageRank,
                'Must be in the range of <0, 1>.'
            );
        }

        $this->humanScore = $humanScore;
        $this->pageRank = $pageRank;
        $this->keywords = $keywords;
        $this->data = $data;
    }

    public function getHumanScore(): float
    {
        return $this->humanScore;
    }

    public function getPageRank(): float
    {
        return $this->pageRank;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function get(string ...$keys)
    {
        $items = $this->data;
        foreach ($keys as $key) {
            if (!is_array($items) || !array_key_exists($key, $items)) {
                return null;
            }
            $items = $items[$key];
        }

        return $items;
    }
}
