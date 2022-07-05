<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\BidStrategyDeleteCommand;
use App\Application\Command\BidStrategyUpdateCommand;
use App\Application\DTO\BidStrategyDeleteDTO;
use App\Application\DTO\BidStrategyUpdateDTO;
use App\Application\Exception\ValidationException;
use App\Domain\ValueObject\PaymentCalculatorConfig;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BidStrategyController extends AbstractController
{
    private BidStrategyUpdateCommand $updateCommand;

    private BidStrategyDeleteCommand $deleteCommand;

    private LoggerInterface $logger;

    public function __construct(
        BidStrategyUpdateCommand $updateCommand,
        BidStrategyDeleteCommand $deleteCommand,
        LoggerInterface $logger
    ) {
        $this->updateCommand = $updateCommand;
        $this->deleteCommand = $deleteCommand;
        $this->logger = $logger;
    }

    public function updateBidStrategies(Request $request): Response
    {
        $this->logger->debug('Call update bid strategies endpoint');

        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new BidStrategyUpdateDTO($input);
        } catch (ValidationException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $this->updateCommand->execute($dto);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function deleteBidStrategies(Request $request): Response
    {
        $this->logger->debug('Call delete bid strategies endpoint');

        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new BidStrategyDeleteDTO($input);
        } catch (ValidationException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $this->deleteCommand->execute($dto);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
