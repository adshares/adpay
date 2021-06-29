<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;

final class EventType
{
    public const CLICK = 'click';
    public const CONVERSION = 'conversion';
    public const VIEW = 'view';

    /** @var string */
    private $type;

    public function __construct(string $type)
    {
        if ($type !== self::CLICK && $type !== self::CONVERSION && $type !== self::VIEW) {
            throw InvalidArgumentException::fromArgument('type', $type);
        }

        $this->type = $type;
    }

    public static function createClick(): self
    {
        return new self(self::CLICK);
    }

    public static function createConversion(): self
    {
        return new self(self::CONVERSION);
    }

    public static function createView(): self
    {
        return new self(self::VIEW);
    }

    public function isClick(): bool
    {
        return $this->type === self::CLICK;
    }

    public function isConversion(): bool
    {
        return $this->type === self::CONVERSION;
    }

    public function isView(): bool
    {
        return $this->type === self::VIEW;
    }

    public function toString(): string
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
