<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Service;

use Adshares\AdPay\Application\Service\CampaignUpdater;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\ValueObject\IdCollection;

class DoctrineCampaignUpdater implements CampaignUpdater
{
    public function update(CampaignCollection $campaigns): void
    {
    }

    public function delete(IdCollection $ids): void
    {
    }
}
