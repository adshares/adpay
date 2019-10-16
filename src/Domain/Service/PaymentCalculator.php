<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Service;

use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Payment;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;

class PaymentCalculator
{



    public function __construct(PaymentReport $report, CampaignCollection $campaigns)
    {
    }

    public function calculate(
        iterable $views,
        iterable $clicks,
        iterable $conversions
    ): iterable {
        yield new Payment(1, EventType::createView(), new Id('6000000000000000000000000000000f'));
    }
}
