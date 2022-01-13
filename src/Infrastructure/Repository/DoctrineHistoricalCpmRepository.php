<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\HistoricalCpm;
use Adshares\AdPay\Domain\Model\HistoricalCpmCollection;
use Adshares\AdPay\Domain\Repository\HistoricalCpmRepository;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Infrastructure\Mapper\HistoricalCpmMapper;
use DateTimeInterface;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Types;

final class DoctrineHistoricalCpmRepository extends DoctrineModelUpdater implements HistoricalCpmRepository
{
    public function fetch(int $reportId, Id $campaignId): ?HistoricalCpm
    {
        try {
            $row =
                $this->db->fetchAssociative(
                    sprintf(
                        <<<SQL
SELECT *
FROM %s
WHERE report_id < :report_id
  AND campaign_id = :campaign_id
ORDER BY report_id DESC
LIMIT 1;
SQL,
                        HistoricalCpmMapper::table()
                    ),
                    ['report_id' => $reportId, 'campaign_id' => $campaignId->toBin()],
                    ['report_id' => Types::INTEGER, 'campaign_id' => Types::BINARY],
                );
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        return $row !== false ? HistoricalCpmMapper::fill($row) : null;
    }

    public function saveAll(HistoricalCpmCollection $historicalCpmCollection): void
    {
        try {
            foreach ($historicalCpmCollection as $historicalCpm) {
                $id = $this->fetchHistoricalCpmId($historicalCpm->getReportId(), $historicalCpm->getCampaignId());

                if ($id === null) {
                    $this->db->insert(
                        HistoricalCpmMapper::table(),
                        HistoricalCpmMapper::map($historicalCpm),
                        HistoricalCpmMapper::types()
                    );
                } else {
                    $this->db->update(
                        HistoricalCpmMapper::table(),
                        HistoricalCpmMapper::map($historicalCpm),
                        ['id' => $id],
                        HistoricalCpmMapper::types()
                    );
                }
            }
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }
    }

    /**
     * @throws DBALException
     */
    private function fetchHistoricalCpmId(int $reportId, Id $campaignId): ?int
    {
        $id = $this->db->fetchOne(
            sprintf(
                <<<SQL
SELECT id FROM %s
WHERE report_id=:report_id AND campaign_id=:campaign_id
SQL,
                HistoricalCpmMapper::table()
            ),
            ['report_id' => $reportId, 'campaign_id' => $campaignId->toBin()],
            ['report_id' => Types::INTEGER, 'campaign_id' => Types::BINARY],
        );

        return $id !== false ? (int)$id : null;
    }

    public function deleteByTime(DateTimeInterface $timeEnd): int
    {
        try {
            $query = sprintf('DELETE FROM %s WHERE created_at <= ?', HistoricalCpmMapper::table());
            $params = [$timeEnd->getTimestamp()];
            $types = [Types::DATETIME_MUTABLE];

            $this->db->beginTransaction();
            $r = $this->db->executeStatement($query, $params, $types);
            $this->db->commit();
            return $r;
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }
    }
}
