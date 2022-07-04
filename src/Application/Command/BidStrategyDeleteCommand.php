<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\BidStrategyDeleteDTO;
use App\Domain\Repository\BidStrategyRepository;
use Psr\Log\LoggerInterface;

final class BidStrategyDeleteCommand
{
    private BidStrategyRepository $bidStrategyRepository;

    private LoggerInterface $logger;

    public function __construct(
        BidStrategyRepository $bidStrategyRepository,
        LoggerInterface $logger
    ) {
        $this->bidStrategyRepository = $bidStrategyRepository;
        $this->logger = $logger;
    }

    public function execute(BidStrategyDeleteDTO $dto): int
    {
        $this->logger->debug('Running delete bid strategy command');
        $result = $this->bidStrategyRepository->deleteAll($dto->getIds());
        $this->logger->info(sprintf('%d bid strategies deleted', $result));

        return $result;
    }
}
