<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidArgumentException;

final class BannerType
{
    public const IMAGE = 'image';

    public const HTML = 'html';

    public const DIRECT = 'direct';

    public const VIDEO = 'video';

    public const MODEL = 'model';

    private string $type;

    public function __construct(string $type)
    {
        if (!in_array($type, [self::IMAGE, self::HTML, self::DIRECT, self::VIDEO, self::MODEL])) {
            throw InvalidArgumentException::fromArgument('type', $type);
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
