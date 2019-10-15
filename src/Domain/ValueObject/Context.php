<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;

class Context
{
    /** @var float */
    private $humanScore;

    /** @var array */
    private $keywords;

    /* @var array */
    private $data;

    public function __construct(float $humanScore, array $keywords = [], array $data = [])
    {
        if ($humanScore < 0 || $humanScore > 1) {
            throw InvalidArgumentException::fromArgument(
                'human score',
                (string)$humanScore,
                'Must be in the range of <0, 1>.'
            );
        }

        $this->humanScore = $humanScore;
        $this->keywords = $keywords;
        $this->data = $data;
    }

    public function getHumanScore(): float
    {
        return $this->humanScore;
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
