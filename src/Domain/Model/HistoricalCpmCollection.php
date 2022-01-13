<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class HistoricalCpmCollection extends ArrayCollection
{
    public function __construct(HistoricalCpm ...$elements)
    {
        parent::__construct($elements);
    }
}
