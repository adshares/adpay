<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\UpdateDataException;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use Adshares\AdPay\Infrastructure\Mapper\PaymentReportMapper;
use Doctrine\DBAL\DBALException;

final class DoctrinePaymentReportRepository extends DoctrineModelUpdater implements PaymentReportRepository
{
    public function fetch(int $id): PaymentReport
    {
        try {
            $result =
                $this->db->fetchAssoc(sprintf('SELECT * FROM %s WHERE id = ?', PaymentReportMapper::table()), [$id]);
        } catch (DBALException $exception) {
            throw new UpdateDataException($exception->getMessage());
        }

        if ($result !== false) {
            $report = PaymentReportMapper::fill($result);
        } else {
            $report = new PaymentReport($id, PaymentReportStatus::createIncomplete());
            $this->save($report);
        }

        return $report;
    }

    public function save(PaymentReport $report): void
    {
        try {
            $this->upsert(
                PaymentReportMapper::table(),
                $report->getId(),
                PaymentReportMapper::map($report),
                PaymentReportMapper::types()
            );
        } catch (DBALException $exception) {
            throw new UpdateDataException($exception->getMessage());
        }
    }
}
