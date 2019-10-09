<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use function explode;
use function preg_match;

class Size
{
    /** @var int */
    private $width;

    /** @var int */
    private $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public static function fromString(string $size): self
    {
        if (!preg_match('/^\d+x\d+$/', $size)) {
            throw InvalidArgumentException::fromArgument('size', $size, 'We support only [WIDTH]x[HEIGHT].');
        }

        $size = explode('x', $size);

        return new self((int)$size[0], (int)$size[1]);
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
        return $this->width.'x'.$this->height;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
