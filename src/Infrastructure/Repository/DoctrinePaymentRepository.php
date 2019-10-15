<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\UpdateDataException;
use Adshares\AdPay\Domain\Model\PaymentCollection;
use Adshares\AdPay\Domain\Repository\PaymentRepository;
use Adshares\AdPay\Infrastructure\Mapper\PaymentMapper;
use DateTimeInterface;
use Doctrine\DBAL\DBALException;

final class DoctrinePaymentRepository extends DoctrineModelUpdater implements PaymentRepository
{
    public function deleteTimeInterval(
        ?DateTimeInterface $timeStart,
        ?DateTimeInterface $timeEnd
    ): void {
    }

    public function saveAll(PaymentCollection $events): int
    {
        return 0;
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
            throw new UpdateDataException($exception->getMessage());
        }

        while ($row = $result->fetch()) {
            yield PaymentMapper::fillRaw($row);
        }
    }
}
