<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\DTO\PaymentFetchDTO;
use Adshares\AdPay\Application\Exception\FetchingException;
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
