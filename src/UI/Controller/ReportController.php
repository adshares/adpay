<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\ReportFetchCommand;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ReportController extends AbstractController
{
    public function __construct(
        private readonly ReportFetchCommand $reportFetchCommand,
        private readonly LoggerInterface $logger
    ) {
    }

    private function validateRequest(Request $request): void
    {
        $ids = $request->get('ids');

        if ($ids !== null) {
            if (!is_array($ids)) {
                throw new UnprocessableEntityHttpException('Ids must be an array');
            }
            foreach ($ids as $id) {
                if (!preg_match('/^\d+$/', $id)) {
                    throw new UnprocessableEntityHttpException('Id must be numeric');
                }
            }
        }
    }

    public function find(Request $request): Response
    {
        $this->logger->debug('Call find reports endpoint');
        $this->validateRequest($request);
        $ids = array_map(fn($id) => (int)$id, $request->get('ids', []));
        $dto = $this->reportFetchCommand->execute(...$ids);
        return new JsonResponse(['data' => $dto->getReports()]);
    }
}
