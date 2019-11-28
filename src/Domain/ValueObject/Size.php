<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use function preg_match;

class Size
{
    /** @var int */
    private $width;

    /** @var int */
    private $height;

    /** @var string */
    private $size;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public static function fromString(string $size): self
    {
        $matches = [];
        if (preg_match('/^(\d+)x(\d+)$/', $size, $matches)) {
            $item = new self((int)$matches[1], (int)$matches[2]);
        } else {
            $item = new self(0, 0);
        }
        $item->size = $size;

        return $item;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function toString(): string
    {
        return $this->size;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
