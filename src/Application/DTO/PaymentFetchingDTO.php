<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

final class PaymentFetchingDTO
{
    /** @var bool */
    private $prepared;

    /** @var iterable */
    private $payments;

    public function __construct(bool $prepared, iterable $payments)
    {
        $this->prepared = $prepared;
        $this->payments = $payments;
    }

    public function isPrepared(): bool
    {
        return $this->prepared;
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
