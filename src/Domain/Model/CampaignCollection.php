<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

final class CampaignCollection extends Collection
{
    public function __construct(Campaign ...$campaigns)
    {
        parent::__construct($campaigns);
    }
}
