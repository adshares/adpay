<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Service;

use Adshares\AdPay\Domain\Repository\BidStrategyRepository;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use Adshares\AdPay\Domain\Repository\HistoricalCpmRepository;
use Adshares\AdPay\Domain\ValueObject\PaymentCalculatorConfig;

class PaymentCalculatorFactory
{
    private CampaignRepository $campaignRepository;

    private BidStrategyRepository $bidStrategyRepository;

    private HistoricalCpmRepository $historicalCpmRepository;

    private PaymentCalculatorConfig $config;

    public function __construct(
        CampaignRepository $campaignRepository,
        BidStrategyRepository $bidStrategyRepository,
        HistoricalCpmRepository $historicalCpmRepository,
        PaymentCalculatorConfig $config
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->bidStrategyRepository = $bidStrategyRepository;
        $this->historicalCpmRepository = $historicalCpmRepository;
        $this->config = $config;
    }

    public function createPaymentCalculator(): PaymentCalculator
    {
        $campaigns = $this->campaignRepository->fetchAll();
        $bidStrategies = $this->bidStrategyRepository->fetchAll();

        return new PaymentCalculator(
            $campaigns,
            $bidStrategies,
            $this->historicalCpmRepository,
            $this->config
        );
    }
}
