<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\CampaignCollection;
use App\Domain\ValueObject\IdCollection;

interface CampaignRepository
{
    public function fetchAll(): CampaignCollection;

    public function saveAll(CampaignCollection $campaigns): int;

    public function deleteAll(IdCollection $ids): int;
}
