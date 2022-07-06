<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\CampaignUpdateDTO;
use App\Domain\Repository\CampaignRepository;
use Psr\Log\LoggerInterface;

final class CampaignUpdateCommand
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

    public function execute(CampaignUpdateDTO $dto): int
    {
        $this->logger->debug('Running update campaigns command');
        $result = $this->campaignRepository->saveAll($dto->getCampaigns());
        $this->logger->info(sprintf('%d campaigns updated', $result));

        return $result;
    }
}
