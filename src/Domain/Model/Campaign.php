<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\Id;
use DateTimeInterface;

final class Campaign
{
    /** @var Id */
    private $id;

    /** @var Id */
    private $advertiserId;

    /** @var DateTimeInterface */
    private $timeStart;

    /** @var DateTimeInterface|null */
    private $timeEnd;

    /** @var Budget */
    private $budget;

    /** @var BannerCollection */
    private $banners;

    /** @var array<string> */
    private $filters;

    /** @var ConversionCollection */
    private $conversions;

    /** @var DateTimeInterface|null */
    private $deletedAt;

    /**
     * @param array<string> $filters
     */
    public function __construct(
        Id $id,
        Id $advertiserId,
        DateTimeInterface $timeStart,
        ?DateTimeInterface $timeEnd,
        Budget $budget,
        BannerCollection $banners,
        array $filters,
        ConversionCollection $conversions
    ) {
        if ($timeEnd !== null && $timeStart > $timeEnd) {
            throw InvalidArgumentException::fromArgument(
                'time start',
                $timeStart->format(DateTimeInterface::ATOM),
                sprintf('Time start must be greater than end date (%s).', $timeEnd->format(DateTimeInterface::ATOM))
            );
        }

        $this->id = $id;
        $this->advertiserId = $advertiserId;
        $this->timeStart = $timeStart;
        $this->timeEnd = $timeEnd;
        $this->budget = $budget;
        $this->banners = $banners;
        $this->filters = [
            'exclude' => $filters['exclude'] ?? [],
            'require' => $filters['require'] ?? [],
        ];
        $this->conversions = $conversions;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getAdvertiserId(): Id
    {
        return $this->advertiserId;
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

    public function getMaxCpc(): ?int
    {
        return $this->budget->getMaxCpc();
    }

    public function getMaxCpm(): ?int
    {
        return $this->budget->getMaxCpm();
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

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }
}
