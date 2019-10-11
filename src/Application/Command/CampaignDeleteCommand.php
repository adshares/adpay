<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\DTO\CampaignDeleteDTO;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use Psr\Log\LoggerInterface;

final class CampaignDeleteCommand
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

    public function execute(CampaignDeleteDTO $dto): int
    {
        return $this->campaignRepository->deleteAll($dto->getIds());
    }
}
