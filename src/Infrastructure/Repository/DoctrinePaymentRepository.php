<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\Payment;
use Adshares\AdPay\Domain\Repository\PaymentRepository;
use Adshares\AdPay\Infrastructure\Mapper\PaymentMapper;
use DateTimeInterface;
use Doctrine\DBAL\DBALException;

final class DoctrinePaymentRepository extends DoctrineModelUpdater implements PaymentRepository
{
    public function deleteByTime(
        ?DateTimeInterface $timeStart,
        ?DateTimeInterface $timeEnd
    ): void {
    }

    public function save(Payment $payment): void
    {
    }

    public function deleteByReportId(int $reportId): void
    {
    }

    public function fetchByReportId(int $reportId, ?int $limit = null, ?int $offset = null): iterable
    {
        $query = sprintf('SELECT * FROM %s WHERE report_id = ?', PaymentMapper::table());

        if ($limit !== null) {
            $query .= sprintf(' LIMIT %d', $limit);
            if ($offset !== null) {
                $query .= sprintf(' OFFSET %d', $offset);
            }
        }

        try {
            $result = $this->db->executeQuery($query, [$reportId]);
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        while ($row = $result->fetch()) {
            yield PaymentMapper::fillRaw($row);
        }
    }
}
