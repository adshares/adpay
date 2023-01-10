<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\PaymentReportFetchDTO;
use App\Domain\Repository\PaymentReportRepository;
use App\Domain\ValueObject\PaymentReportStatus;
use Psr\Log\LoggerInterface;

final class ReportFetchCompletedCommand
{
    public function __construct(
        private readonly PaymentReportRepository $paymentReportRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): PaymentReportFetchDTO
    {
        $this->logger->debug('Running fetch completed report command');
        $reports = $this->paymentReportRepository->fetchByStatus(PaymentReportStatus::createComplete());
        return new PaymentReportFetchDTO($reports);
    }
}
