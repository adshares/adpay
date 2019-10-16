<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Service;

use Adshares\AdPay\Domain\Model\CampaignCollection;

class PaymentCalculator
{
    public function __construct(CampaignCollection $campaigns)
    {
    }

    public function calculate(
        iterable $views,
        iterable $clicks,
        iterable $conversions
    ): iterable {
        return [];
    }
}
