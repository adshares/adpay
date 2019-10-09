<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class EventCollection extends ArrayCollection
{
    public function __construct(Event ...$views)
    {
        parent::__construct($views);
    }
}
