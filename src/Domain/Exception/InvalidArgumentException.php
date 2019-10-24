<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException
{
    public static function fromArgument(string $name, string $value = '', string $restrictions = ''): self
    {
        $message = sprintf('Given %s (%s) is invalid.', $name, $value);
        if (!empty($restrictions)) {
            $message .= ' '.$restrictions;
        }

        return new self($message);
    }
}
