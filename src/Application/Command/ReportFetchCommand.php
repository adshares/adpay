<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\PaymentReportFetchDTO;
use App\Domain\Repository\PaymentReportRepository;
use App\Domain\ValueObject\PaymentReportStatus;
use Psr\Log\LoggerInterface;

final class ReportFetchCommand
{
    private PaymentReportRepository $paymentReportRepository;

    private LoggerInterface $logger;

    public function __construct(
        PaymentReportRepository $paymentReportRepository,
        LoggerInterface $logger
    ) {
        $this->paymentReportRepository = $paymentReportRepository;
        $this->logger = $logger;
    }

    public function execute(
        bool $calculated = true,
        bool $complete = false,
        bool $incomplete = false
    ): PaymentReportFetchDTO {
        $this->logger->debug('Running fetch report command');

        $statuses = [];
        if ($calculated) {
            $statuses[] = PaymentReportStatus::createCalculated();
        }
        if ($complete) {
            $statuses[] = PaymentReportStatus::createComplete();
        }
        if ($incomplete) {
            $statuses[] = PaymentReportStatus::createIncomplete();
        }

        $reports = $this->paymentReportRepository->fetchByStatus(...$statuses);

        return new PaymentReportFetchDTO($reports);
    }
}
