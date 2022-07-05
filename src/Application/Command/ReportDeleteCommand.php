<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Repository\PaymentReportRepository;
use DateTimeInterface;
use Psr\Log\LoggerInterface;

final class ReportDeleteCommand
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

    public function execute(DateTimeInterface $dateTo): int
    {
        $this->logger->debug('Running delete payment report command');
        $result = $this->paymentReportRepository->deleteByTime(null, $dateTo);
        $this->logger->info(sprintf('%d payment reports deleted', $result));

        return $result;
    }
}
