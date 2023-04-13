<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidArgumentException;

class Context
{
    private float $humanScore;

    private float $pageRank;

    private ?int $adsTxt;

    private array $keywords;

    private array $data;

    public function __construct(
        float $humanScore,
        float $pageRank,
        ?int $adsTxt = null,
        array $keywords = [],
        array $data = [],
    ) {
        if ($humanScore < 0 || $humanScore > 1) {
            throw InvalidArgumentException::fromArgument(
                'human score',
                (string)$humanScore,
                'Must be in the range of <0, 1>.'
            );
        }
        if (($pageRank < 0 && $pageRank !== -1.0) || $pageRank > 1) {
            throw InvalidArgumentException::fromArgument(
                'page rank',
                (string)$pageRank,
                'Must be in the range of <0, 1> or equal -1.'
            );
        }
        if (null !== $adsTxt && 0 !== $adsTxt && 1 !== $adsTxt) {
            throw InvalidArgumentException::fromArgument(
                'ads txt',
                (string)$adsTxt,
                'Must be 0, 1 or null.'
            );
        }

        $this->humanScore = $humanScore;
        $this->pageRank = $pageRank;
        $this->adsTxt = $adsTxt;
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

    public function getAdsTxt(): ?int
    {
        return $this->adsTxt;
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
