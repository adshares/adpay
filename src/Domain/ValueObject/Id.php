<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use function preg_match;

final class Id
{
    /** @var string */
    private $id;

    public function __construct(string $id)
    {
        if (!preg_match('/^[0-9a-fA-F]{32}$/', $id)) {
            throw new InvalidArgumentException('id', $id);
        }

        $this->id = $id;
    }

    public function equals(Id $id): bool
    {
        return $this->id === $id->id;
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
