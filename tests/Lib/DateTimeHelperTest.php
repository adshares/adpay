<?php

declare(strict_types=1);

namespace App\Tests\Lib;

use App\Lib\DateTimeHelper;
use App\Lib\Exception\DateTimeException;
use PHPUnit\Framework\TestCase;
use DateTimeInterface;

final class DateTimeHelperTest extends TestCase
{
    public function testFromTimestamp(): void
    {
        $date = DateTimeHelper::fromTimestamp(1546344000);
        $this->assertEquals('2019-01-01T12:00:00+00:00', $date->format(DateTimeInterface::ATOM));
    }

    public function testInvalidTimestamp(): void
    {
        $this->expectException(DateTimeException::class);
        DateTimeHelper::fromTimestamp(0);
    }

    public function testFromString(): void
    {
        $date = DateTimeHelper::fromString('2019-01-01T12:00:00+00:00');
        $this->assertEquals('2019-01-01T12:00:00+00:00', $date->format(DateTimeInterface::ATOM));
    }

    public function testInvalidString(): void
    {
        $this->expectException(DateTimeException::class);
        DateTimeHelper::fromString('2019-50-50');
    }
}
