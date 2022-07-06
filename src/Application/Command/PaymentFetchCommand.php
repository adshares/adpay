<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\PaymentFetchDTO;
use App\Application\Exception\FetchingException;
use App\Domain\Model\PaymentReport;
use App\Domain\Repository\PaymentReportRepository;
use App\Domain\Repository\PaymentRepository;
use Psr\Log\LoggerInterface;

final class PaymentFetchCommand
{
    private PaymentReportRepository $paymentReportRepository;

    private PaymentRepository $paymentRepository;

    private LoggerInterface $logger;

    public function __construct(
        PaymentReportRepository $paymentReportRepository,
        PaymentRepository $paymentRepository,
        LoggerInterface $logger
    ) {
        $this->paymentReportRepository = $paymentReportRepository;
        $this->paymentRepository = $paymentRepository;
        $this->logger = $logger;
    }

    public function execute(int $timestamp, ?int $limit = null, ?int $offset = null): PaymentFetchDTO
    {
        $this->logger->debug('Running fetch payments command');

        $report = $this->paymentReportRepository->fetch(PaymentReport::timestampToId($timestamp));

        if (!$report->isComplete()) {
            throw new FetchingException(sprintf('Report #%d is not complete yet.', $report->getId()));
        }

        if (!$report->isCalculated()) {
            return new PaymentFetchDTO(false, []);
        }

        $payments = $this->paymentRepository->fetchByReportId($report->getId(), $limit, $offset);

        return new PaymentFetchDTO(true, $payments);
    }
}
