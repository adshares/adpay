<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\ValueObject;

final class PaymentCalculatorConfig
{
    private float $humanScoreThreshold = 0.5;

    private float $conversionHumanScoreThreshold = 0.4;

    private float $budgetFactor = 0.2;

    private int $serverCpm = 7 * 10 ** 11;

    public function __construct(
        array $config = []
    ) {
        $this->humanScoreThreshold = (float)($config['humanScoreThreshold'] ?? $this->humanScoreThreshold);
        $this->conversionHumanScoreThreshold =
                (float)($config['conversionHumanScoreThreshold'] ?? $this->conversionHumanScoreThreshold);
        $this->budgetFactor = (float)($config['budgetFactor'] ?? $this->budgetFactor);
        $this->serverCpm = (int)($config['serverCpm'] ?? $this->serverCpm);
    }

    public function getHumanScoreThreshold(): float
    {
        return $this->humanScoreThreshold;
    }

    public function getConversionHumanScoreThreshold(): float
    {
        return $this->conversionHumanScoreThreshold;
    }

    public function getBudgetFactor(): float
    {
        return $this->budgetFactor;
    }

    public function getServerCpm(): int
    {
        return $this->serverCpm;
    }
}
