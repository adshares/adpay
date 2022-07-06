<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\CampaignDeleteCommand;
use App\Application\Command\CampaignUpdateCommand;
use App\Application\DTO\CampaignDeleteDTO;
use App\Application\DTO\CampaignUpdateDTO;
use App\Application\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CampaignController extends AbstractController
{
    private CampaignUpdateCommand $updateCommand;

    private CampaignDeleteCommand $deleteCommand;

    private LoggerInterface $logger;

    public function __construct(
        CampaignUpdateCommand $updateCommand,
        CampaignDeleteCommand $deleteCommand,
        LoggerInterface $logger
    ) {
        $this->updateCommand = $updateCommand;
        $this->deleteCommand = $deleteCommand;
        $this->logger = $logger;
    }

    public function updateCampaigns(Request $request): Response
    {
        $this->logger->debug('Call update campaigns endpoint');

        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new CampaignUpdateDTO($input);
        } catch (ValidationException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $this->updateCommand->execute($dto);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function deleteCampaigns(Request $request): Response
    {
        $this->logger->debug('Call delete campaigns endpoint');

        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new CampaignDeleteDTO($input);
        } catch (ValidationException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $this->deleteCommand->execute($dto);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
