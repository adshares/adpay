<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\Payment;
use Adshares\AdPay\Domain\Repository\PaymentRepository;
use Adshares\AdPay\Infrastructure\Mapper\PaymentMapper;
use Doctrine\DBAL\DBALException;

final class DoctrinePaymentRepository extends DoctrineModelUpdater implements PaymentRepository
{
    public function save(Payment $payment): void
    {
        try {
            $this->db->insert(
                PaymentMapper::table(),
                PaymentMapper::map($payment),
                PaymentMapper::types()
            );
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }
    }

    public function deleteByReportId(int $reportId): int
    {
        try {
            return $this->db->executeUpdate(
                sprintf('DELETE FROM %s WHERE report_id = ?', PaymentMapper::table()),
                [$reportId]
            );
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }
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
