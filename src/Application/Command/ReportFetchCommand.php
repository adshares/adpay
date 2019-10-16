<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\DTO\PaymentReportFetchDTO;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use Psr\Log\LoggerInterface;

final class ReportFetchCommand
{
    /** @var PaymentReportRepository */
    private $paymentReportRepository;

    /** @var LoggerInterface */
    private $logger;

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
