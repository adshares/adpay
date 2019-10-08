<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class ConversionEventCollection extends ArrayCollection
{
    public function __construct(ConversionEvent ...$conversions)
    {
        parent::__construct($conversions);
    }
}
