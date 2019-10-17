<?php declare(strict_types = 1);

namespace Adshares\AdPay\UI\Controller;

use Adshares\AdPay\Application\Command\PaymentFetchCommand;
use Adshares\AdPay\Application\Command\ReportCalculateCommand;
use Adshares\AdPay\Application\Exception\FetchingException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PaymentController extends AbstractController
{
    const MAX_LIMIT = 100000;

    /** @var PaymentFetchCommand */
    private $paymentFetchCommand;

    /** @var ReportCalculateCommand */
    private $paymentCalculateCommand;

    /** @var LoggerInterface */
    private $logger;

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
        $offset = $request->get('offset');

        if ($offset !== null) {
            $offset = (int)$offset;
        }

        if ($offset === 0 && $recalculate) {
            try {
                $this->paymentCalculateCommand->execute($timestamp, $force);
            } catch (FetchingException $exception) {
                throw new NotFoundHttpException($exception->getMessage());
            }
        }

        $dto = $this->paymentFetchCommand->execute($timestamp, $limit, $offset);

        if (!$dto->isCalculated()) {
            throw new NotFoundHttpException('Report is not calculated yet');
        }

        return new JsonResponse(['payments' => $dto->getPayments()]);
    }
}
