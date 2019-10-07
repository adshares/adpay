<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Service;

use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\ValueObject\IdCollection;

interface CampaignUpdater
{
    public function update(CampaignCollection $campaigns): void;

    public function delete(IdCollection $ids): void;
}
