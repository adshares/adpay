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

        $events =
            $this->eventRepository->fetchByTime(
                $report->getTimeStart(),
                $report->getTimeEnd()
            );

        $calculator = $this->createCalculator();
        $count = 0;
        foreach ($calculator->calculate($events) as $payment) {
            $this->paymentRepository->saveRaw($report->getId(), $payment);
            ++$count;
        }

        $report->markAsCalculated();
        $this->paymentReportRepository->save($report);

        $this->logger->info(sprintf('%d payments calculated', $count));

        return $count;
    }

    private function createCalculator(): PaymentCalculator
    {
        $campaigns = $this->campaignRepository->fetchAll();
        $config = [
            'humanScoreThreshold' => getenv('HUMAN_SCORE_THRESHOLD'),
        ];

        return new PaymentCalculator($campaigns, $config);
    }
}
