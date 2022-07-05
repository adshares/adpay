<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\PaymentFetchCommand;
use App\Application\Command\ReportCalculateCommand;
use App\Application\Exception\FetchingException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PaymentController extends AbstractController
{
    public const MAX_LIMIT = 100000;

    private PaymentFetchCommand $paymentFetchCommand;

    private ReportCalculateCommand $paymentCalculateCommand;

    private LoggerInterface $logger;

    public function __construct(
        PaymentFetchCommand $paymentFetchCommand,
        ReportCalculateCommand $paymentCalculateCommand,
        LoggerInterface $logger
    ) {
        $this->paymentFetchCommand = $paymentFetchCommand;
        $this->paymentCalculateCommand = $paymentCalculateCommand;
        $this->logger = $logger;
    }

    private function validateRequest(Request $request): void
    {
        $limit = $request->get('limit');
        $offset = $request->get('offset');

        if ($limit !== null) {
            if (!preg_match('/^\d+$/', $limit)) {
                throw new UnprocessableEntityHttpException('Limit must be numeric');
            }
            if ((int)$limit > self::MAX_LIMIT) {
                throw new UnprocessableEntityHttpException(sprintf('Limit must be lower than %d', self::MAX_LIMIT));
            }
        }

        if ($offset !== null) {
            if (!preg_match('/^\d+$/', $offset)) {
                throw new UnprocessableEntityHttpException('Offset must be numeric');
            }
        }
    }

    public function find(int $timestamp, Request $request): Response
    {
        $this->logger->debug('Call find payments endpoint');

        $this->validateRequest($request);

        $force = (bool)$request->get('force', false);
        $recalculate = (bool)$request->get('recalculate', false);
        $limit = (int)$request->get('limit', self::MAX_LIMIT);
        $offset = (int)$request->get('offset', 0);

        try {
            if ($offset === 0 && $recalculate) {
                $this->paymentCalculateCommand->execute($timestamp, $force);
            }
            $dto = $this->paymentFetchCommand->execute($timestamp, $limit, $offset);
        } catch (FetchingException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (!$dto->isCalculated()) {
            throw new NotFoundHttpException('Report is not calculated yet');
        }

        return new JsonResponse(['payments' => $dto->getPayments()]);
    }
}
