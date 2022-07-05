<?php

declare(strict_types=1);

namespace App\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class PaymentReportCollection extends ArrayCollection
{
    public function __construct(PaymentReport ...$reports)
    {
        parent::__construct($reports);
    }
}
