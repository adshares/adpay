<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Repository\BidStrategyRepository;
use App\Domain\Repository\CampaignRepository;
use App\Domain\Repository\CampaignCostRepository;
use App\Domain\ValueObject\PaymentCalculatorConfig;

class PaymentCalculatorFactory
{
    private CampaignRepository $campaignRepository;

    private BidStrategyRepository $bidStrategyRepository;

    private CampaignCostRepository $campaignCostRepository;

    private PaymentCalculatorConfig $config;

    public function __construct(
        CampaignRepository $campaignRepository,
        BidStrategyRepository $bidStrategyRepository,
        CampaignCostRepository $campaignCostRepository,
        PaymentCalculatorConfig $config
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->bidStrategyRepository = $bidStrategyRepository;
        $this->campaignCostRepository = $campaignCostRepository;
        $this->config = $config;
    }

    public function createPaymentCalculator(): PaymentCalculator
    {
        $campaigns = $this->campaignRepository->fetchAll();
        $bidStrategies = $this->bidStrategyRepository->fetchAll();

        return new PaymentCalculator(
            $campaigns,
            $bidStrategies,
            $this->campaignCostRepository,
            $this->config
        );
    }
}
