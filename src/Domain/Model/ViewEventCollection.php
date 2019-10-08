<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class ViewEventCollection extends ArrayCollection
{
    public function __construct(ViewEvent ...$views)
    {
        parent::__construct($views);
    }
}
