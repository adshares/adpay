<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Model\PaymentReport;
use App\Domain\Model\PaymentReportCollection;

final class PaymentReportFetchDTO
{
    public function __construct(private readonly PaymentReportCollection $reports)
    {
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

    public function getReports(): array
    {
        $list = [];
        foreach ($this->reports as $report) {
            /** @var $report PaymentReport */
            $list[] = [
                'id' => $report->getId(),
                'status' => $report->getStatus()->toString(),
            ];
        }
        return $list;
    }
}
