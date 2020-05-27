<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Exception;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class InvalidArgumentExceptionTest extends TestCase
{
    public function testInstanceOfInvalidArgumentException(): void
    {
        $e = InvalidArgumentException::fromArgument('input');
        $this->assertEquals('Given input () is invalid.', $e->getMessage());

        $e = InvalidArgumentException::fromArgument('input', 'abc');
        $this->assertEquals('Given input (abc) is invalid.', $e->getMessage());

        $e = InvalidArgumentException::fromArgument('input', 'abc', 'Must be good.');
        $this->assertEquals('Given input (abc) is invalid. Must be good.', $e->getMessage());
    }
}
