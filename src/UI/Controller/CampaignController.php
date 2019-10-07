<?php declare(strict_types = 1);

namespace Adshares\AdPay\UI\Controller;

use Adshares\AdPay\Application\DTO\CampaignUpdateDTO;
use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CampaignController extends AbstractController
{

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
    }

    public function upsert(Request $request): Response
    {
        $this->logger->debug('Running post campaigns command');

        $content = json_decode($request->getContent(), true);

        if ($content === null || !isset($content['campaigns'])) {
            throw new BadRequestHttpException('Incorrect data');
        }

        try {
            $dto = new CampaignUpdateDTO($content['campaigns']);
        } catch (ValidationDTOException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
//
//        $this->campaignUpdater->update($dto->getCampaignCollection());

        $this->logger->debug('Campaigns updated');

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function delete(?string $id, Request $request): Response
    {
        return new Response('ok ' . $id);
    }
}
