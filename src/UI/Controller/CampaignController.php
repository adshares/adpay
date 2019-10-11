<?php declare(strict_types = 1);

namespace Adshares\AdPay\UI\Controller;

use Adshares\AdPay\Application\Command\CampaignDeleteCommand;
use Adshares\AdPay\Application\Command\CampaignUpdateCommand;
use Adshares\AdPay\Application\DTO\CampaignDeleteDTO;
use Adshares\AdPay\Application\DTO\CampaignUpdateDTO;
use Adshares\AdPay\Application\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CampaignController extends AbstractController
{
    /** @var CampaignUpdateCommand */
    private $updateCommand;

    /** @var CampaignDeleteCommand */
    private $deleteCommand;

    /** @var LoggerInterface */
    private $logger;

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
        $this->logger->debug('Running post campaigns command');

        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new CampaignUpdateDTO($input);
        } catch (ValidationException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        try {
            $result = $this->updateCommand->execute($dto);
        } catch (ValidationException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $this->logger->info(sprintf('%d campaigns updated', $result));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function deleteCampaigns(Request $request): Response
    {
        $this->logger->debug('Running delete campaigns command');

        $input = json_decode($request->getContent(), true);
        if ($input === null || !is_array($input)) {
            throw new UnprocessableEntityHttpException('Invalid input data');
        }

        try {
            $dto = new CampaignDeleteDTO($input);
        } catch (ValidationException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        try {
            $result = $this->deleteCommand->execute($dto);
        } catch (ValidationException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $this->logger->info(sprintf('%d campaigns deleted', $result));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
