<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Repository;

use Adshares\AdPay\Domain\Model\HistoricalCpm;
use Adshares\AdPay\Domain\Model\HistoricalCpmCollection;
use Adshares\AdPay\Domain\ValueObject\Id;
use DateTimeInterface;

interface HistoricalCpmRepository
{
    public function fetch(int $reportId, Id $campaignId): ?HistoricalCpm;

    public function saveAll(HistoricalCpmCollection $historicalCpmCollection): void;

    public function deleteByTime(DateTimeInterface $timeEnd): int;
}
