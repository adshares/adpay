<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\Exception\AdPayRuntimeException;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\Id;
use DateTimeInterface;

final class Campaign
{
    /** @var Id */
    private $campaignId;
    /** @var DateTimeInterface */
    private $timeStart;
    /** @var DateTimeInterface|null */
    private $timeEnd;
    /** @var BannerCollection */
    private $banners;
    /** @var array<string> */
    private $keywords;
    /** @var array<string> */
    private $filters;
    /** @var Budget */
    private $budget;

    /**
     * @param array<string> $keywords
     * @param array<string> $filters
     */
    public function __construct(
        Id $campaignId,
        DateTimeInterface $timeStart,
        ?DateTimeInterface $timeEnd,
        BannerCollection $banners,
        array $keywords,
        array $filters,
        Budget $budget
    ) {
        if ($timeEnd && $timeStart > $timeEnd) {
            throw new AdPayRuntimeException(sprintf(
                'Time start (%s) must be greater than end date (%s).',
                $timeStart->toString(),
                $timeEnd->toString()
            ));
        }

        $this->campaignId = $campaignId;
        $this->timeStart = $timeStart;
        $this->timeEnd = $timeEnd;
        $this->banners = $banners;
        $this->keywords = $keywords;
        $this->filters = [
            'exclude' => $filters['exclude'] ?? [],
            'require' => $filters['require'] ?? [],
        ];
        $this->budget = $budget;
    }

    public function getId(): string
    {
        return $this->campaignId->toString();
    }

    public function getTimeStart(): int
    {
        return $this->timeStart->getTimestamp();
    }

    public function getTimeEnd(): ?int
    {
        if (!$this->timeEnd) {
            return null;
        }

        return $this->timeEnd->getTimestamp();
    }

    public function getBanners(): BannerCollection
    {
        return $this->banners;
    }

    /** @return array<string> */
    public function getKeywords(): array
    {
        return $this->keywords;
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

    public function getBudget(): int
    {
        return $this->budget->getBudget();
    }

    public function getMaxCpc(): ?int
    {
        return $this->budget->getMaxCpc();
    }

    public function getMaxCpm(): ?int
    {
        return $this->budget->getMaxCpm();
    }
}
