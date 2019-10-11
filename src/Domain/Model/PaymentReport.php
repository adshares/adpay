<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;

final class PaymentReport
{
    /** @var int */
    private $id;

    /** @var PaymentReportStatus */
    private $status;

    private $intervals;

    public function __construct(
        int $id,
        PaymentReportStatus $status
    ) {
        $this->id = $id;
        $this->status = $status;
    }
}
