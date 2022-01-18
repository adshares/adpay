<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Repository;

use Adshares\AdPay\Domain\Model\CampaignCost;
use Adshares\AdPay\Domain\Model\CampaignCostCollection;
use Adshares\AdPay\Domain\ValueObject\Id;
use DateTimeInterface;

interface CampaignCostRepository
{
    public function fetch(int $reportId, Id $campaignId): ?CampaignCost;

    public function saveAll(CampaignCostCollection $campaignCostCollection): int;

    public function deleteByTime(DateTimeInterface $timeEnd): int;
}
