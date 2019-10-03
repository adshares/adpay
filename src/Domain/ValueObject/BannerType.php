<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\AdPayRuntimeException;

class BannerType
{
    public const IMAGE = 'image';
    public const HTML = 'html';

    /** @var string */
    private $type;

    public function __construct(string $type)
    {
        if ($type !== self::IMAGE && $type !== self::HTML) {
            throw new AdPayRuntimeException(sprintf('Given banner type (%s) is not valid.', $type));
        }

        $this->type = $type;
    }

    public static function createImage(): self
    {
        return new self(self::IMAGE);
    }

    public static function createHtml(): self
    {
        return new self(self::HTML);
    }

    public function isImage(): bool
    {
        return $this->type === self::IMAGE;
    }

    public function isHtml(): bool
    {
        return $this->type === self::HTML;
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
