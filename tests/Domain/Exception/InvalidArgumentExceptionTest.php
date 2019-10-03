<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model\Exception;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SizeTest extends TestCase
{
    public function testInstanceOfInvalidArgumentException(): void
    {
        $e = new InvalidArgumentException('input');
        $this->assertEquals('Given input () is invalid.', $e->getMessage());

        $e = new InvalidArgumentException('input', 'abc');
        $this->assertEquals('Given input (abc) is invalid.', $e->getMessage());

        $e = new InvalidArgumentException('input', 'abc', 'Must be good.');
        $this->assertEquals('Given input (abc) is invalid. Must be good.', $e->getMessage());
    }
}
