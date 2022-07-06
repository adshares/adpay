<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\BidStrategyCollection;
use App\Domain\ValueObject\IdCollection;

interface BidStrategyRepository
{
    public function fetchAll(): BidStrategyCollection;

    public function saveAll(BidStrategyCollection $bidStrategies): int;

    public function deleteAll(IdCollection $ids): int;
}
