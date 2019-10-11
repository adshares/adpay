<?php declare(strict_types = 1);

namespace Adshares\AdPay\UI\Controller;

use Adshares\AdPay\Application\DTO\CampaignDeleteDTO;
use Adshares\AdPay\Application\DTO\CampaignUpdateDTO;
use Adshares\AdPay\Application\Exception\InvalidDataException;
use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Application\Service\CampaignUpdater;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CampaignController extends AbstractController
{
    /** @var CampaignUpdater */
    private $campaignUpdater;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(CampaignUpdater $campaignUpdater, LoggerInterface $logger)
    {
        $this->campaignUpdater = $campaignUpdater;
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
        } catch (ValidationDTOException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        try {
            $result = $this->campaignUpdater->update($dto->getCampaigns());
        } catch (InvalidDataException $exception) {
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
        } catch (ValidationDTOException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        try {
            $result = $this->campaignUpdater->delete($dto->getIds());
        } catch (InvalidDataException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $this->logger->info(sprintf('%d campaigns deleted', $result));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
