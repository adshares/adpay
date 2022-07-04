<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Exception\DomainRepositoryException;
use App\Domain\Model\CampaignCost;
use App\Domain\Model\CampaignCostCollection;
use App\Domain\Repository\CampaignCostRepository;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Mapper\CampaignCostMapper;
use DateTimeInterface;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Types;

final class DoctrineCampaignCostRepository extends DoctrineModelUpdater implements CampaignCostRepository
{
    public function fetch(int $reportId, Id $campaignId): ?CampaignCost
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
                        CampaignCostMapper::table()
                    ),
                    ['report_id' => $reportId, 'campaign_id' => $campaignId->toBin()],
                    ['report_id' => Types::INTEGER, 'campaign_id' => Types::BINARY],
                );
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        return $row !== false ? CampaignCostMapper::fill($row) : null;
    }

    public function saveAll(CampaignCostCollection $campaignCostCollection): int
    {
        $count = 0;
        try {
            foreach ($campaignCostCollection as $campaignCost) {
                $id = $this->fetchId($campaignCost->getReportId(), $campaignCost->getCampaignId());

                if ($id === null) {
                    $this->db->insert(
                        CampaignCostMapper::table(),
                        CampaignCostMapper::map($campaignCost),
                        CampaignCostMapper::types()
                    );
                } else {
                    $this->db->update(
                        CampaignCostMapper::table(),
                        CampaignCostMapper::map($campaignCost),
                        ['id' => $id],
                        CampaignCostMapper::types()
                    );
                }
                ++$count;
            }
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        return $count;
    }

    /**
     * @throws DBALException
     */
    private function fetchId(int $reportId, Id $campaignId): ?int
    {
        $id = $this->db->fetchOne(
            sprintf(
                <<<SQL
SELECT id FROM %s
WHERE report_id=:report_id AND campaign_id=:campaign_id
SQL,
                CampaignCostMapper::table()
            ),
            ['report_id' => $reportId, 'campaign_id' => $campaignId->toBin()],
            ['report_id' => Types::INTEGER, 'campaign_id' => Types::BINARY],
        );

        return $id !== false ? (int)$id : null;
    }

    public function deleteByTime(DateTimeInterface $timeEnd): int
    {
        try {
            $query = sprintf('DELETE FROM %s WHERE updated_at <= ?', CampaignCostMapper::table());
            $params = [$timeEnd];
            $types = [Types::DATETIME_MUTABLE];

            $this->db->beginTransaction();
            $r = $this->db->executeStatement($query, $params, $types);
            $this->db->commit();
            return $r;
        } catch (DBALException $exception) {
            $this->db->rollBack();
            throw new DomainRepositoryException($exception->getMessage());
        }
    }
}
