<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

final class PaymentFetchDTO
{
    /** @var bool */
    private $calculated;

    /** @var iterable */
    private $payments;

    public function __construct(bool $calculated, iterable $payments)
    {
        $this->calculated = $calculated;
        $this->payments = $payments;
    }

    public function isCalculated(): bool
    {
        return $this->calculated;
    }

    public function getPayments(): array
    {
        $list = [];
        foreach ($this->payments as $payment) {
            $list[] = [
                'event_id' => $payment['event_id'],
                'event_type' => $payment['event_type'],
                'status' => $payment['status'],
                'value' => $payment['value'],
            ];
        }

        return $list;
    }
}
