<?php

declare(strict_types=1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\Exception\FetchingException;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Repository\EventRepository;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\Repository\PaymentRepository;
use Adshares\AdPay\Domain\Service\PaymentCalculatorFactory;
use DateTimeInterface;
use Psr\Log\LoggerInterface;

final class ReportCalculateCommand
{
    private const BATCH_SIZE = 1000;

    private PaymentReportRepository $paymentReportRepository;

    private PaymentRepository $paymentRepository;

    private EventRepository $eventRepository;

    private PaymentCalculatorFactory $paymentCalculatorFactory;

    private LoggerInterface $logger;

    public function __construct(
        PaymentReportRepository $paymentReportRepository,
        PaymentRepository $paymentRepository,
        EventRepository $eventRepository,
        PaymentCalculatorFactory $paymentCalculatorFactory,
        LoggerInterface $logger
    ) {
        $this->paymentReportRepository = $paymentReportRepository;
        $this->paymentRepository = $paymentRepository;
        $this->eventRepository = $eventRepository;
        $this->paymentCalculatorFactory = $paymentCalculatorFactory;
        $this->logger = $logger;
    }

    public function execute(int $timestamp, bool $force = false): int
    {
        $this->logger->debug(sprintf('Running calculate payments command %s', $force ? '[forced]' : ''));

        $reportId = PaymentReport::timestampToId($timestamp);
        $report = $this->paymentReportRepository->fetch($reportId);

        if (!$report->isComplete() && !$force) {
            throw new FetchingException(sprintf('Report #%d is not complete yet.', $reportId));
        }

        $this->logger->info(
            sprintf(
                'Calculating report #%d from %s to %s',
                $reportId,
                $report->getTimeStart()->format(DateTimeInterface::ATOM),
                $report->getTimeEnd()->format(DateTimeInterface::ATOM)
            )
        );

        $this->paymentRepository->deleteByReportId($report->getId());

        $events = $this->eventRepository->fetchByTime($report->getTimeStart(), $report->getTimeEnd());

        $calculator = $this->paymentCalculatorFactory->createPaymentCalculator();
        $count = 0;
        $payments = [];
        foreach ($calculator->calculate($reportId, $events) as $payment) {
            $payments[] = $payment;
            ++$count;
            if ($count % self::BATCH_SIZE === 0) {
                $this->paymentRepository->saveAllRaw($report->getId(), $payments);
                $payments = [];
            }
        }
        $this->paymentRepository->saveAllRaw($report->getId(), $payments);

        $report->markAsCalculated();
        $this->paymentReportRepository->save($report);

        $this->logger->info(sprintf('%d payments calculated', $count));

        return $count;
    }
}
