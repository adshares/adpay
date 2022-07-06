<?php

declare(strict_types=1);

namespace App\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class CampaignCollection extends ArrayCollection
{
    public function __construct(Campaign ...$campaigns)
    {
        parent::__construct($campaigns);
    }
}
