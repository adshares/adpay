<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\DTO;

use Adshares\AdPay\Application\DTO\PaymentReportFetchDTO;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Model\PaymentReportCollection;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
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
                self::report(1),
                self::report(2)
            )
        );
        $this->assertCount(2, $dto->getReportIds());
        $this->assertEquals([1, 2], $dto->getReportIds());
    }

    private static function report(int $id): PaymentReport
    {
        return new PaymentReport($id, PaymentReportStatus::createComplete());
    }
}
