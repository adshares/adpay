<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class BannerCollection extends ArrayCollection
{
    public function __construct(Banner ...$banners)
    {
        parent::__construct($banners);
    }
}
