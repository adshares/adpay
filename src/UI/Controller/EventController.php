<?php declare(strict_types = 1);

namespace Adshares\AdPay\UI\Controller;

use Adshares\AdPay\Application\DTO\ClickEventUpdateDTO;
use Adshares\AdPay\Application\DTO\ConversionEventUpdateDTO;
use Adshares\AdPay\Application\DTO\ViewEventUpdateDTO;
use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Application\Service\EventUpdater;
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

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EventUpdater $eventUpdater, LoggerInterface $logger)
    {
        $this->eventUpdater = $eventUpdater;
        $this->logger = $logger;
    }

    public function upsertViews(Request $request): Response
    {
        $this->logger->debug('Running post views command');

        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new ViewEventUpdateDTO($input);
        } catch (ValidationDTOException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $result = $this->eventUpdater->updateViews($dto->getTimeStart(), $dto->getTimeEnd(), $dto->getEvents());

        $this->logger->info(sprintf('%d views updated', $result));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function upsertClicks(Request $request): Response
    {
        $this->logger->debug('Running post clicks command');

        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new ClickEventUpdateDTO($input);
        } catch (ValidationDTOException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $result = $this->eventUpdater->updateClicks($dto->getTimeStart(), $dto->getTimeEnd(), $dto->getEvents());

        $this->logger->info(sprintf('%d clicks updated', $result));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function upsertConversions(Request $request): Response
    {
        $this->logger->debug('Running post conversions command');

        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new ConversionEventUpdateDTO($input);
        } catch (ValidationDTOException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $result = $this->eventUpdater->updateConversions($dto->getTimeStart(), $dto->getTimeEnd(), $dto->getEvents());

        $this->logger->info(sprintf('%d conversions updated', $result));
        $this->logger->info(sprintf('%d conversions updated', $result));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
