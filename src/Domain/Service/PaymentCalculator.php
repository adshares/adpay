<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Service;

use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Payment;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;

class PaymentCalculator
{
    public function __construct(PaymentReport $report, CampaignCollection $campaigns)
    {
    }

    public function calculate(
        iterable $events
    ): iterable {
        foreach ($events as $event) {
            yield new Payment(
                1,
                new EventType($event['type']),
                new Id($event['id']),
                new PaymentStatus(PaymentStatus::ACCEPTED)
            );
        }
    }
}
