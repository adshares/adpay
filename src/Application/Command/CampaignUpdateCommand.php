<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\DTO\CampaignUpdateDTO;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use Psr\Log\LoggerInterface;

final class CampaignUpdateCommand
{
    /** @var CampaignRepository */
    private $campaignRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        CampaignRepository $campaignRepository,
        LoggerInterface $logger
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->logger = $logger;
    }

    public function execute(CampaignUpdateDTO $dto): int
    {
        return $this->campaignRepository->saveAll($dto->getCampaigns());
    }
}
