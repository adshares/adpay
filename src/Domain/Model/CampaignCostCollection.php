<?php

declare(strict_types=1);

namespace App\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class CampaignCostCollection extends ArrayCollection
{
    public function __construct(CampaignCost ...$elements)
    {
        parent::__construct($elements);
    }
}
