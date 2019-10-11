<?php declare(strict_types = 1);

namespace Adshares\AdPay\UI\Controller;

use Adshares\AdPay\Application\DTO\ClickEventUpdateDTO;
use Adshares\AdPay\Application\DTO\ConversionEventUpdateDTO;
use Adshares\AdPay\Application\DTO\EventUpdateDTO;
use Adshares\AdPay\Application\DTO\ViewEventUpdateDTO;
use Adshares\AdPay\Application\Exception\InvalidDataException;
use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Application\Service\EventUpdater;
use Adshares\AdPay\Application\Service\ReportUpdater;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class EventController extends AbstractController
{
    /** @var EventUpdater */
    private $eventUpdater;

    /** @var ReportUpdater */
    private $reportUpdater;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EventUpdater $eventUpdater, ReportUpdater $reportUpdater, LoggerInterface $logger)
    {
        $this->eventUpdater = $eventUpdater;
        $this->reportUpdater = $reportUpdater;
        $this->logger = $logger;
    }

    private function parseRequest(Request $request, string $dto): EventUpdateDTO
    {
        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new $dto($input);
        } catch (ValidationDTOException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        return $dto;
    }

    private function updateEvents(EventUpdateDTO $dto): int
    {
        try {
            $result = $this->eventUpdater->update($dto->getTimeStart(), $dto->getTimeEnd(), $dto->getEvents());
            $this->reportUpdater->noticeEvents($dto->getEvents()->getType(), $dto->getTimeStart(), $dto->getTimeEnd());
        } catch (InvalidDataException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        return $result;
    }

    public function updateViews(Request $request): Response
    {
        $this->logger->debug('Running post views command');
        $result = $this->updateEvents($this->parseRequest($request, ViewEventUpdateDTO::class));
        $this->logger->info(sprintf('%d views updated', $result));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function updateClicks(Request $request): Response
    {
        $this->logger->debug('Running post clicks command');
        $result = $this->updateEvents($this->parseRequest($request, ClickEventUpdateDTO::class));
        $this->logger->info(sprintf('%d clicks updated', $result));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function updateConversions(Request $request): Response
    {
        $this->logger->debug('Running post conversions command');
        $result = $this->updateEvents($this->parseRequest($request, ConversionEventUpdateDTO::class));
        $this->logger->info(sprintf('%d conversions updated', $result));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
