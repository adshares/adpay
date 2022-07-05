<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\CampaignCost;
use App\Domain\Model\CampaignCostCollection;
use App\Domain\ValueObject\Id;
use DateTimeInterface;

interface CampaignCostRepository
{
    public function fetch(int $reportId, Id $campaignId): ?CampaignCost;

    public function saveAll(CampaignCostCollection $campaignCostCollection): int;

    public function deleteByTime(DateTimeInterface $timeEnd): int;
}
