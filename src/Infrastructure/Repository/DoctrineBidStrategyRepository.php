<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\BidStrategy;
use Adshares\AdPay\Domain\Model\BidStrategyCollection;
use Adshares\AdPay\Domain\Repository\BidStrategyRepository;
use Adshares\AdPay\Infrastructure\Mapper\BidStrategyMapper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

final class DoctrineBidStrategyRepository extends DoctrineModelUpdater implements BidStrategyRepository
{
    public function fetchAll(): BidStrategyCollection
    {
        $query = sprintf('SELECT * FROM %s', BidStrategyMapper::table());

        try {
            $rows = $this->db->fetchAll($query);
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        $bidStrategies = new BidStrategyCollection();
        foreach ($rows as $row) {
            $bidStrategies->add(BidStrategyMapper::fill($row));
        }

        return $bidStrategies;
    }

    public function saveAll(BidStrategyCollection $bidStrategies): int
    {
        if (0 === $bidStrategies->count()) {
            return 0;
        }

        $count = 0;

        $mapIds = [];
        foreach ($bidStrategies as $bidStrategy) {
            /*  @var $bidStrategy BidStrategy */
            $id = $bidStrategy->getId()->toBin();
            if (!isset($mapIds[$id])) {
                $mapIds[$id] = 1;
            }
        }
        $ids = array_keys($mapIds);
        $deleteQuery = sprintf('DELETE FROM %s WHERE bid_strategy_id IN (?)', BidStrategyMapper::table());

        $this->db->beginTransaction();
        try {
            $this->db->executeQuery($deleteQuery, [$ids], [Connection::PARAM_STR_ARRAY]);

            foreach ($bidStrategies as $bidStrategy) {
                /*  @var $bidStrategy BidStrategy */
                $this->db->insert(
                    BidStrategyMapper::table(),
                    BidStrategyMapper::map($bidStrategy),
                    BidStrategyMapper::types()
                );

                ++$count;
            }
            $this->db->commit();
        } catch (DBALException $exception) {
            $this->db->rollBack();
            throw new DomainRepositoryException($exception->getMessage());
        }

        return $count;
    }
}
