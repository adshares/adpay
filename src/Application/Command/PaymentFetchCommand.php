<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\DTO\PaymentFetchingDTO;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\Repository\PaymentRepository;
use Psr\Log\LoggerInterface;

final class PaymentFetchCommand
{
    /** @var PaymentReportRepository */
    private $paymentReportRepository;

    /** @var PaymentRepository */
    private $paymentRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        PaymentReportRepository $paymentReportRepository,
        PaymentRepository $paymentRepository,
        LoggerInterface $logger
    ) {
        $this->paymentReportRepository = $paymentReportRepository;
        $this->paymentRepository = $paymentRepository;
        $this->logger = $logger;
    }

    public function execute(int $timestamp, ?int $limit = null, ?int $offset = null): PaymentFetchingDTO
    {
        $this->logger->debug('Running fetch payments command');

        $report = $this->paymentReportRepository->fetch(PaymentReport::timestampToId($timestamp));

        if (!$report->isPrepared()) {
            return new PaymentFetchingDTO(false, []);
        }

        $payments = $this->paymentRepository->fetchByReportId($report->getId(), $limit, $offset);

        return new PaymentFetchingDTO(true, $payments);
    }
}
