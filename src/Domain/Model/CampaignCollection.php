<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class CampaignCollection extends ArrayCollection
{
    public function __construct(Campaign ...$campaigns)
    {
        parent::__construct($campaigns);
    }
}
