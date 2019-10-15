<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class PaymentCollection extends ArrayCollection
{
    public function __construct(Payment ...$payments)
    {
        parent::__construct($payments);
    }
}
