<?php declare(strict_types = 1);

namespace Adshares\AdPay\UI\Controller;

use Adshares\AdPay\Application\DTO\CampaignDeleteDTO;
use Adshares\AdPay\Application\DTO\CampaignUpdateDTO;
use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Application\Service\CampaignUpdater;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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

    public function upsert(Request $request): Response
    {
        $this->logger->debug('Running post campaigns command');

        $content = json_decode($request->getContent(), true);
        if ($content === null || !isset($content['campaigns'])) {
            throw new UnprocessableEntityHttpException('Incorrect data');
        }

        try {
            $dto = new CampaignUpdateDTO($content['campaigns']);
        } catch (ValidationDTOException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $this->campaignUpdater->update($dto->getCampaigns());

        $this->logger->debug('Campaigns updated');

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function delete(Request $request): Response
    {
        $this->logger->debug('Running delete campaigns command');

        $content = json_decode($request->getContent(), true);
        if ($content === null || !isset($content['campaigns'])) {
            throw new UnprocessableEntityHttpException('Incorrect data');
        }

        try {
            $dto = new CampaignDeleteDTO($content['campaigns']);
        } catch (ValidationDTOException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $this->campaignUpdater->delete($dto->getIds());

        $this->logger->debug('Campaigns deleted');

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
