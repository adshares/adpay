<?php declare(strict_types = 1);

namespace Adshares\AdPay\UI\Controller;

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

//        try {
//            $dto = new CampaignUpdateDto($content['campaigns']);
//        } catch (ValidationDtoException $exception) {
//            throw new BadRequestHttpException($exception->getMessage());
//        }
//
//        $this->campaignUpdater->update($dto->getCampaignCollection());

        $this->logger->debug('Campaigns updated');

        return new JsonResponse([], Response::HTTP_NO_CONTENT);




//        if (empty($data)) {
//            throw new BadRequestHttpException('No campaign data to update.');
//        }
//
//        if (!is_array($data)) {
//            $data = [$data];
//        }
//
//        foreach ($data as $campaign) {
//            dump($campaign);
//            exit;
//
//        }
    }

    public function delete(?string $id, Request $request): Response
    {
        return new Response('ok ' . $id);
    }
}
