<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Model\PaymentReport;
use App\Domain\Model\PaymentReportCollection;

final class PaymentReportFetchDTO
{
    private PaymentReportCollection $reports;

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
