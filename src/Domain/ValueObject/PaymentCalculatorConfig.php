<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class PaymentCalculatorConfig
{
    private float $humanScoreThreshold = 0.5;

    private float $conversionHumanScoreThreshold = 0.4;

    private float $metaverseHumanScoreThreshold = 0.4;

    private float $autoCpmBudgetThreshold = 0.2;

    private int $autoCpmDefault = 7 * 10 ** 11;

    public function __construct(
        array $config = []
    ) {
        $this->humanScoreThreshold = (float)($config['humanScoreThreshold'] ?? $this->humanScoreThreshold);
        $this->conversionHumanScoreThreshold =
            (float)($config['conversionHumanScoreThreshold'] ?? $this->conversionHumanScoreThreshold);
        $this->autoCpmBudgetThreshold = (float)($config['autoCpmBudgetThreshold'] ?? $this->autoCpmBudgetThreshold);
        $this->autoCpmDefault = (int)($config['autoCpmDefault'] ?? $this->autoCpmDefault);
    }

    public function getHumanScoreThreshold(): float
    {
        return $this->humanScoreThreshold;
    }

    public function getConversionHumanScoreThreshold(): float
    {
        return $this->conversionHumanScoreThreshold;
    }

    public function getMetaverseHumanScoreThreshold(): float
    {
        return $this->metaverseHumanScoreThreshold;
    }

    public function getAutoCpmBudgetThreshold(): float
    {
        return $this->autoCpmBudgetThreshold;
    }

    public function getAutoCpmDefault(): int
    {
        return $this->autoCpmDefault;
    }
}
