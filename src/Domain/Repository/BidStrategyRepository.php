<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Repository;

use Adshares\AdPay\Domain\Model\BidStrategyCollection;

interface BidStrategyRepository
{
    public function fetchAll(): BidStrategyCollection;

    public function saveAll(BidStrategyCollection $bidStrategies): int;
}
