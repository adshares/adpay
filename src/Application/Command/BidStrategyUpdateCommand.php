<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\BidStrategyUpdateDTO;
use App\Domain\Repository\BidStrategyRepository;
use Psr\Log\LoggerInterface;

final class BidStrategyUpdateCommand
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

    public function execute(BidStrategyUpdateDTO $dto): int
    {
        $this->logger->debug('Running update bid strategy command');
        $result = $this->bidStrategyRepository->saveAll($dto->getBidStrategies());
        $this->logger->info(sprintf('%d bid strategies updated', $result));

        return $result;
    }
}
