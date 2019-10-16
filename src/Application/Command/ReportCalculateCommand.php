<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\Exception\FetchingException;
use Adshares\AdPay\Domain\Model\Payment;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use Adshares\AdPay\Domain\Repository\EventRepository;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\Repository\PaymentRepository;
use Adshares\AdPay\Domain\Service\PaymentCalculator;
use Adshares\AdPay\Domain\ValueObject\EventType;
use DateTimeInterface;
use Psr\Log\LoggerInterface;

final class ReportCalculateCommand
{
    /** @var PaymentReportRepository */
    private $paymentReportRepository;

    /** @var PaymentRepository */
    private $paymentRepository;

    /** @var CampaignRepository */
    private $campaignRepository;

    /** @var EventRepository */
    private $eventRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        PaymentReportRepository $paymentReportRepository,
        PaymentRepository $paymentRepository,
        CampaignRepository $campaignRepository,
        EventRepository $eventRepository,
        LoggerInterface $logger
    ) {
        $this->paymentReportRepository = $paymentReportRepository;
        $this->paymentRepository = $paymentRepository;
        $this->campaignRepository = $campaignRepository;
        $this->eventRepository = $eventRepository;
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

        $campaigns = $this->campaignRepository->fetchAll();
        $views =
            $this->eventRepository->fetchByTime(
                EventType::createView(),
                $report->getTimeStart(),
                $report->getTimeEnd()
            );
        $clicks =
            $this->eventRepository->fetchByTime(
                EventType::createClick(),
                $report->getTimeStart(),
                $report->getTimeEnd()
            );
        $conversions =
            $this->eventRepository->fetchByTime(
                EventType::createConversion(),
                $report->getTimeStart(),
                $report->getTimeEnd()
            );

        $calculator = new PaymentCalculator($report, $campaigns);

        $count = 0;
        foreach ($calculator->calculate($views, $clicks, $conversions) as $payment) {
            /** @var $payment Payment */
            $this->paymentRepository->save($payment);
            ++$count;
        }

        $this->logger->info(sprintf('%d payments calculated', $count));

        return $count;
    }
}
