<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Model\PaymentReportCollection;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use Adshares\AdPay\Infrastructure\Mapper\PaymentReportMapper;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

final class DoctrinePaymentReportRepository extends DoctrineModelUpdater implements PaymentReportRepository
{
    public function fetch(int $id): PaymentReport
    {
        try {
            $result =
                $this->db->fetchAssoc(sprintf('SELECT * FROM %s WHERE id = ?', PaymentReportMapper::table()), [$id]);
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        if ($result !== false) {
            $report = PaymentReportMapper::fill($result);
        } else {
            $report = new PaymentReport($id, PaymentReportStatus::createIncomplete());
            $this->save($report);
        }

        return $report;
    }

    public function fetchByStatus(PaymentReportStatus ...$statuses): PaymentReportCollection
    {
        $conditions = [];
        foreach ($statuses as $status) {
            /** @var $status PaymentReportStatus */
            $conditions[] = $status->getStatus();
        }

        try {
            $result = $this->db->fetchAll(
                sprintf('SELECT * FROM %s WHERE status IN (?)', PaymentReportMapper::table()),
                [$conditions],
                [Connection::PARAM_STR_ARRAY]
            );
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        $reports = new PaymentReportCollection();
        foreach ($result as $row) {
            $reports->add(PaymentReportMapper::fill($row));
        }

        return $reports;
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
            throw new DomainRepositoryException($exception->getMessage());
        }
    }

    public function deleteByTime(?DateTimeInterface $timeStart = null, ?DateTimeInterface $timeEnd = null): int
    {
        try {
            $params = [];
            $types = [];
            $query = sprintf('DELETE FROM %s WHERE 1=1', PaymentReportMapper::table());

            if ($timeStart !== null) {
                $query .= ' AND id >= ?';
                $params[] = $timeStart->getTimestamp();
            }
            if ($timeEnd !== null) {
                $query .= ' AND id <= ?';
                $params[] = $timeEnd->getTimestamp();
            }

            $this->db->beginTransaction();
            $r = $this->db->executeUpdate($query, $params, $types);
            $this->db->commit();
            return $r;
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }
    }
}
