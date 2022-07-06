<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\CampaignDeleteDTO;
use App\Domain\Repository\CampaignRepository;
use Psr\Log\LoggerInterface;

final class CampaignDeleteCommand
{
    private CampaignRepository $campaignRepository;

    private LoggerInterface $logger;

    public function __construct(
        CampaignRepository $campaignRepository,
        LoggerInterface $logger
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->logger = $logger;
    }

    public function execute(CampaignDeleteDTO $dto): int
    {
        $this->logger->debug('Running delete campaigns command');
        $result = $this->campaignRepository->deleteAll($dto->getIds());
        $this->logger->info(sprintf('%d campaigns deleted', $result));

        return $result;
    }
}
