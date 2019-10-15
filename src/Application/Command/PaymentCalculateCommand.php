<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\DTO\PaymentFetchingDTO;
use Adshares\AdPay\Application\Exception\FetchingException;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use Adshares\AdPay\Domain\Repository\EventRepository;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\Repository\PaymentRepository;
use Adshares\AdPay\Domain\Service\PaymentCalculator;
use Adshares\AdPay\Domain\ValueObject\EventType;
use DateTimeInterface;
use Psr\Log\LoggerInterface;

final class PaymentCalculateCommand
{
    private const HOUR = 3600;

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

    public function execute(int $timestamp, bool $force = false): PaymentFetchingDTO
    {
        $this->logger->debug(sprintf('Running calculate payments command %s', $force ? '[forced]' : ''));

        $reportId = PaymentReport::timestampToId($timestamp);
        $report = $this->paymentReportRepository->fetch($reportId);

        if (!$report->isComplete() && !$force) {
            throw new FetchingException(sprintf('Report [%d] is not complete yet', $reportId));
        }

        $this->logger->info(
            sprintf(
                'Calculating report [%d] from %s to %s',
                $reportId,
                $report->getTimeStart()->format(DateTimeInterface::ATOM),
                $report->getTimeEnd()->format(DateTimeInterface::ATOM)
            )
        );

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

        $calculator = new PaymentCalculator($campaigns);
        $payments = $calculator->calculate($views, $clicks, $conversions);

        $this->paymentRepository->deleteByReportId($report->getId());
//        $this->paymentRepository->saveAll($payments);

        $this->logger->info(sprintf('%d payments calculated', count($payments)));

        return new PaymentFetchingDTO(true, $payments);
    }
}
