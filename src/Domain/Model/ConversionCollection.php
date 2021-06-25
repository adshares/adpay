<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class ConversionCollection extends ArrayCollection
{
    public function __construct(Conversion ...$conversions)
    {
        parent::__construct($conversions);
    }
}
