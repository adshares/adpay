<?php

declare(strict_types=1);

namespace App\Tests\Application\DTO;

use App\Application\DTO\PaymentReportFetchDTO;
use App\Domain\Model\PaymentReport;
use App\Domain\Model\PaymentReportCollection;
use App\Domain\ValueObject\PaymentReportStatus;
use PHPUnit\Framework\TestCase;

final class PaymentReportFetchDTOTest extends TestCase
{
    public function testEmptyPayments(): void
    {
        $dto = new PaymentReportFetchDTO(new PaymentReportCollection());
        $this->assertEmpty($dto->getReportIds());
    }

    public function testReportIds(): void
    {
        $dto = new PaymentReportFetchDTO(
            new PaymentReportCollection(
                self::report(1, PaymentReportStatus::createComplete()),
                self::report(2, PaymentReportStatus::createCalculated()),
                self::report(3, PaymentReportStatus::createIncomplete())
            )
        );
        $this->assertCount(3, $dto->getReportIds());
        $this->assertEquals([1, 2, 3], $dto->getReportIds());
        $this->assertEquals([
            [
                'id' => 1,
                'status' => 'complete',
            ],
            [
                'id' => 2,
                'status' => 'calculated',
            ],
            [
                'id' => 3,
                'status' => 'incomplete',
            ]
        ], $dto->getReports());
    }

    private static function report(int $id, PaymentReportStatus $status): PaymentReport
    {
        return new PaymentReport($id, $status);
    }
}
