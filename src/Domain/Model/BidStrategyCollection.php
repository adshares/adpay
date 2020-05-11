<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class BidStrategyCollection extends ArrayCollection
{
    public function __construct(BidStrategy ...$bidStrategies)
    {
        parent::__construct($bidStrategies);
    }
}
