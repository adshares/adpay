<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\EventUpdateCommand;
use App\Application\DTO\ClickEventUpdateDTO;
use App\Application\DTO\ConversionEventUpdateDTO;
use App\Application\DTO\EventUpdateDTO;
use App\Application\DTO\ViewEventUpdateDTO;
use App\Application\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class EventController extends AbstractController
{
    private EventUpdateCommand $eventUpdateCommand;

    private LoggerInterface $logger;

    public function __construct(EventUpdateCommand $eventUpdateCommand, LoggerInterface $logger)
    {
        $this->eventUpdateCommand = $eventUpdateCommand;
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
        } catch (ValidationException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        return $dto;
    }

    private function updateEvents(EventUpdateDTO $dto): int
    {
        try {
            $result = $this->eventUpdateCommand->execute($dto);
        } catch (ValidationException $exception) {
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
