<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Repository;

use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\ValueObject\IdCollection;

interface CampaignRepository
{
    public function saveAll(CampaignCollection $campaigns): int;

    public function deleteAll(IdCollection $ids): int;
}
