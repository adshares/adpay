<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\BidStrategy;
use Adshares\AdPay\Domain\Model\BidStrategyCollection;
use Adshares\AdPay\Domain\Repository\BidStrategyRepository;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use Adshares\AdPay\Infrastructure\Mapper\BidStrategyMapper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;

final class DoctrineBidStrategyRepository extends DoctrineModelUpdater implements BidStrategyRepository
{
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

    public function deleteAll(IdCollection $ids): int
    {
        try {
            $result = $this->softDelete(BidStrategyMapper::table(), $ids->toBinArray(), 'bid_strategy_id');
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        return $result;
    }

    public function fetchAll(): BidStrategyCollection
    {
        $query = sprintf(
            'SELECT * FROM %s WHERE deleted_at IS NULL OR deleted_at > NOW() - INTERVAL 32 DAY',
            BidStrategyMapper::table()
        );

        try {
            $rows = $this->db->fetchAllAssociative($query);
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        $bidStrategies = new BidStrategyCollection();
        foreach ($rows as $row) {
            $bidStrategies->add(BidStrategyMapper::fill($row));
        }

        return $bidStrategies;
    }
}
