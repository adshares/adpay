<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class CampaignCostCollection extends ArrayCollection
{
    public function __construct(CampaignCost ...$elements)
    {
        parent::__construct($elements);
    }
}
