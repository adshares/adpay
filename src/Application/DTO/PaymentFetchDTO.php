<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class PaymentFetchDTO
{
    private bool $calculated;

    private iterable $payments;

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
