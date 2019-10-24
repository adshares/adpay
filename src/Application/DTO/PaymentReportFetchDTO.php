<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Model\PaymentReportCollection;

final class PaymentReportFetchDTO
{
    /** @var PaymentReportCollection */
    private $reports;

    public function __construct(PaymentReportCollection $reports)
    {
        $this->reports = $reports;
    }

    public function getReportIds(): array
    {
        $ids = [];
        foreach ($this->reports as $report) {
            /** @var $report PaymentReport */
            $ids[] = $report->getId();
        }

        return $ids;
    }
}
