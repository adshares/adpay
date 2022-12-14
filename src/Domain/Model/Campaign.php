<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\ValueObject\Budget;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Medium;
use DateTimeInterface;

final class Campaign
{
    /** @var array<string> */
    private $filters;

    public function __construct(
        private readonly Id $id,
        private readonly Id $advertiserId,
        private readonly Medium $medium,
        private readonly ?string $vendor,
        private readonly DateTimeInterface $timeStart,
        private readonly ?DateTimeInterface $timeEnd,
        private readonly Budget $budget,
        private readonly BannerCollection $banners,
        array $filters,
        private readonly ConversionCollection $conversions,
        private readonly Id $bidStrategyId,
        private readonly ?DateTimeInterface $deletedAt = null
    ) {
        if ($timeEnd !== null && $timeStart > $timeEnd) {
            throw InvalidArgumentException::fromArgument(
                'time start',
                $timeStart->format(DateTimeInterface::ATOM),
                sprintf('Time start must be greater than end date (%s).', $timeEnd->format(DateTimeInterface::ATOM))
            );
        }
        $this->filters = [
            'exclude' => $filters['exclude'] ?? [],
            'require' => $filters['require'] ?? [],
        ];
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getAdvertiserId(): Id
    {
        return $this->advertiserId;
    }

    public function getMedium(): Medium
    {
        return $this->medium;
    }

    public function isWeb(): bool
    {
        return Medium::Web === $this->medium;
    }

    public function isMetaverse(): bool
    {
        return Medium::Metaverse === $this->medium;
    }

    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    public function getTimeStart(): DateTimeInterface
    {
        return $this->timeStart;
    }

    public function getTimeEnd(): ?DateTimeInterface
    {
        return $this->timeEnd;
    }

    public function getBudget(): Budget
    {
        return $this->budget;
    }

    public function getBudgetValue(): int
    {
        return $this->budget->getValue();
    }

    public function getMaxCpm(): ?int
    {
        return $this->budget->getMaxCpm();
    }

    public function getMaxCpc(): ?int
    {
        return $this->budget->getMaxCpc();
    }

    public function getClickCost(): int
    {
        $cpc = $this->budget->getMaxCpc();

        return $cpc !== null ? $cpc : 0;
    }

    public function getBanners(): BannerCollection
    {
        return $this->banners;
    }

    /** @return array<string> */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /** @return array<string> */
    public function getExcludeFilters(): array
    {
        return $this->filters['exclude'];
    }

    /** @return array<string> */
    public function getRequireFilters(): array
    {
        return $this->filters['require'];
    }

    public function getConversions(): ConversionCollection
    {
        return $this->conversions;
    }

    public function getBidStrategyId(): Id
    {
        return $this->bidStrategyId;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function checkFilters(array $keywords): bool
    {
        foreach ($this->getRequireFilters() as $key => $values) {
            if (!isset($keywords[$key]) || empty(array_intersect($values, $keywords[$key]))) {
                return false;
            }
        }
        foreach ($this->getExcludeFilters() as $key => $values) {
            if (isset($keywords[$key]) && !empty(array_intersect($values, $keywords[$key]))) {
                return false;
            }
        }

        return true;
    }
}
