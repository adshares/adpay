<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\PaymentFetchCommand;
use App\Application\Command\ReportCalculateCommand;
use App\Application\Exception\FetchingException;
use App\Application\Exception\ReportNotFoundException;
use App\Application\Exception\ReportNotCompleteException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentController extends AbstractController
{
    use PaginationAwareTrait;

    private const MAX_LIMIT = 100000;

    public function __construct(
        private readonly PaymentFetchCommand $paymentFetchCommand,
        private readonly ReportCalculateCommand $paymentCalculateCommand,
        private readonly LoggerInterface $logger
    ) {
    }

    public function find(int $timestamp, Request $request): Response
    {
        $this->logger->debug('Call find payments endpoint');

        $this->validatePaginationRequest($request, self::MAX_LIMIT);

        $force = (bool)$request->get('force', false);
        $recalculate = (bool)$request->get('recalculate', false);
        $limit = (int)$request->get('limit', self::MAX_LIMIT);
        $offset = (int)$request->get('offset', 0);

        try {
            if ($offset === 0 && $recalculate) {
                $this->paymentCalculateCommand->execute($timestamp, $force);
            }
            $dto = $this->paymentFetchCommand->execute($timestamp, $limit, $offset);
        } catch (ReportNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (ReportNotCompleteException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (!$dto->isCalculated()) {
            throw new HttpException(425, 'Report is not calculated yet');
        }

        return new JsonResponse(['data' => $dto->getPayments()]);
    }
}
