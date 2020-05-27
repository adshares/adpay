<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\DTO\BidStrategyUpdateDTO;
use Adshares\AdPay\Domain\Repository\BidStrategyRepository;
use Psr\Log\LoggerInterface;

final class BidStrategyUpdateCommand
{
    /** @var BidStrategyRepository */
    private $bidStrategyRepository;

    /** @var LoggerInterface */
    private $logger;

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
