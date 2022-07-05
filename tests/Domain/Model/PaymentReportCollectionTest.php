<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Model\PaymentReport;
use App\Domain\Model\PaymentReportCollection;
use App\Domain\ValueObject\PaymentReportStatus;
use PHPUnit\Framework\TestCase;

final class PaymentReportCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $item1 = self::createPaymentReport(1);
        $item2 = self::createPaymentReport(2);
        $item3 = self::createPaymentReport(3);
        $item4 = self::createPaymentReport(4);

        $this->assertCount(4, new PaymentReportCollection($item1, $item2, $item3, $item4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new PaymentReportCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createPaymentReport(int $id): PaymentReport
    {
        return new PaymentReport($id, new PaymentReportStatus());
    }
}
