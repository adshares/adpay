<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class ClickEventCollection extends ArrayCollection
{
    public function __construct(ClickEvent ...$clicks)
    {
        parent::__construct($clicks);
    }
}
