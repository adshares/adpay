<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;
use Throwable;

class InvalidArgumentException extends BaseInvalidArgumentException
{
    public function __construct(
        string $name,
        string $value = '',
        string $restrictions = '',
        $code = 0,
        Throwable $previous = null
    ) {
        $message = sprintf('Given %s (%s) is invalid.', $name, $value);
        if (!empty($restrictions)) {
            $message .= ' '.$restrictions;
        }

        parent::__construct($message, $code, $previous);
    }
}
