<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Service;

use Adshares\AdPay\Application\Exception\UpdateDataException;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\ValueObject\IdCollection;

interface CampaignUpdater
{
    /**
     * @throws UpdateDataException
     */
    public function update(CampaignCollection $campaigns): int;

    /**
     * @throws UpdateDataException
     */
    public function delete(IdCollection $ids): int;
}
