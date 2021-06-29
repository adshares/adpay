<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Repository\PaymentRepository;
use Adshares\AdPay\Infrastructure\Mapper\PaymentMapper;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;

final class DoctrinePaymentRepository extends DoctrineModelUpdater implements PaymentRepository
{
    public function saveAllRaw(int $reportId, array $payments): int
    {
        foreach ($payments as $key => $payment) {
            $payment['report_id'] = $reportId;
            $payments[$key] = PaymentMapper::map($payment);
        }

        try {
            return $this->insertBatch(
                PaymentMapper::table(),
                $payments,
                PaymentMapper::types()
            );
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }
    }

    public function deleteByReportId(int $reportId): int
    {
        try {
            return $this->db->executeStatement(
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
            while ($row = $result->fetchAssociative()) {
                yield PaymentMapper::fillRaw($row);
            }
        } catch (DBALException | DBALDriverException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }
    }
}
