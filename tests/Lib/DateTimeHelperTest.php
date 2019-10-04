<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Lib;

use Adshares\AdPay\Lib\DateTimeHelper;
use Adshares\AdPay\Lib\Exception\DateTimeException;
use PHPUnit\Framework\TestCase;
use DateTimeInterface;

final class DateTimeHelperTest extends TestCase
{
    public function testFromTimestamp(): void
    {
        $date = DateTimeHelper::createFromTimestamp(1546344000);
        $this->assertEquals('2019-01-01T12:00:00+00:00', $date->format(DateTimeInterface::ATOM));
    }

    public function testFromString(): void
    {
        $date = DateTimeHelper::createFromString('2019-01-01T12:00:00+00:00');
        $this->assertEquals('2019-01-01T12:00:00+00:00', $date->format(DateTimeInterface::ATOM));
    }

    public function testInvalidString(): void
    {
        $this->expectException(DateTimeException::class);
        DateTimeHelper::createFromString('2019-50-50');
    }
}
