<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Application\Exception\ValidationException;
use App\Domain\Exception\InvalidArgumentException;
use App\Domain\Model\Banner;
use App\Domain\Model\BannerCollection;
use App\Domain\Model\Campaign;
use App\Domain\Model\CampaignCollection;
use App\Domain\Model\Conversion;
use App\Domain\Model\ConversionCollection;
use App\Domain\ValueObject\BannerType;
use App\Domain\ValueObject\Budget;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\LimitType;
use App\Domain\ValueObject\Medium;
use App\Lib\DateTimeHelper;
use App\Lib\Exception\DateTimeException;
use TypeError;

final class CampaignUpdateDTO
{
    private CampaignCollection $campaigns;

    public function __construct(array $input)
    {
        $this->validate($input);
        $this->fill($input);
    }

    public function getCampaigns(): CampaignCollection
    {
        return $this->campaigns;
    }

    private function validate(array $input): void
    {
        if (!isset($input['campaigns'])) {
            throw new ValidationException('Field `campaigns` is required.');
        }

        foreach ($input['campaigns'] as $campaign) {
            $this->validateCampaign($campaign);
        }
    }

    private function validateCampaign(array $input): void
    {
        if (!isset($input['id'])) {
            throw new ValidationException('Field `id` is required.');
        }

        if (!isset($input['advertiser_id'])) {
            throw new ValidationException('Field `advertiser_id` is required.');
        }

        if (!isset($input['time_start'])) {
            throw new ValidationException('Field `time_start` is required.');
        }

        if (!isset($input['budget'])) {
            throw new ValidationException('Field `budget` is required.');
        }

        if (!isset($input['bid_strategy_id'])) {
            throw new ValidationException('Field `bid_strategy_id` is required.');
        }

        if (!isset($input['banners'])) {
            throw new ValidationException('Field `banners` is required.');
        }

        if (!is_array($input['banners'])) {
            throw new ValidationException('Field `banners` must be an array.');
        }

        $this->validateBanners($input['banners']);

        if (isset($input['filters'])) {
            if (!is_array($input['filters'])) {
                throw new ValidationException('Field `filters` must be an array.');
            }

            $this->validateFilters($input['filters']);
        }

        if (isset($input['conversions'])) {
            if (!is_array($input['conversions'])) {
                throw new ValidationException('Field `conversions` must be an array.');
            }

            $this->validateConversions($input['conversions']);
        }
    }

    private function validateBanners(array $banners): void
    {
        foreach ($banners as $input) {
            if (empty($input['id'])) {
                throw new ValidationException('Field `banners[][id]` is required.');
            }

            if (empty($input['size'])) {
                throw new ValidationException('Field `banners[][size]` is required.');
            }

            if (empty($input['type'])) {
                throw new ValidationException('Field `banners[][type]` is required.');
            }
        }
    }

    private function validateFilters(array $filters): void
    {
        if (isset($filters['require']) && !is_array($filters['require'])) {
            throw new ValidationException('Field `filters[require]` must be an array.');
        }

        if (isset($filters['exclude']) && !is_array($filters['exclude'])) {
            throw new ValidationException('Field `filters[exclude]` must be an array.');
        }
    }

    private function validateConversions(array $conversions): void
    {
        foreach ($conversions as $input) {
            if (!isset($input['id'])) {
                throw new ValidationException('Field `conversions[][id]` is required.');
            }

            if (!isset($input['limit_type'])) {
                throw new ValidationException('Field `conversions[][limit_type]` is required.');
            }

            if (!isset($input['is_repeatable'])) {
                throw new ValidationException('Field `conversions[][is_repeatable]` is required.');
            }
        }
    }

    protected function fill(array $input): void
    {
        $collection = new CampaignCollection();
        foreach ($input['campaigns'] as $campaign) {
            $collection->add($this->createCampaignModel($campaign));
        }
        $this->campaigns = $collection;
    }

    private function createCampaignModel(array $input): Campaign
    {
        try {
            $campaignId = new Id($input['id']);
            $advertiserId = new Id($input['advertiser_id']);
            $medium = Medium::tryFrom($input['medium'] ?? Medium::Web->value);
            $vendor = $input['vendor'] ?? null;
            $banners = $this->prepareBannerCollection($campaignId, $input['banners']);
            $filters = $this->prepareFilters($input['filters'] ?? []);
            $conversions = $this->prepareConversionCollection($campaignId, $input['conversions'] ?? []);
            $budget =
                new Budget($input['budget'], $input['max_cpm'] ?? null, $input['max_cpc'] ?? null);
            $bidStrategyId = new Id($input['bid_strategy_id']);

            return new Campaign(
                $campaignId,
                $advertiserId,
                $medium,
                $vendor,
                DateTimeHelper::fromTimestamp($input['time_start']),
                isset($input['time_end']) ? DateTimeHelper::fromTimestamp($input['time_end'])
                    : null,
                $budget,
                $banners,
                $filters,
                $conversions,
                $bidStrategyId
            );
        } catch (InvalidArgumentException | DateTimeException | TypeError $exception) {
            throw new ValidationException($exception->getMessage());
        }
    }

    private function prepareFilters(array $filters): array
    {
        return $filters;
    }

    private function prepareBannerCollection(Id $campaignId, array $banners): BannerCollection
    {
        $collection = new BannerCollection();

        foreach ($banners as $input) {
            $banner = new Banner(
                new Id($input['id']),
                $campaignId,
                $input['size'],
                new BannerType($input['type'])
            );

            $collection->add($banner);
        }

        return $collection;
    }

    private function prepareConversionCollection(Id $campaignId, array $conversions): ConversionCollection
    {
        $collection = new ConversionCollection();

        foreach ($conversions as $input) {
            $conversion = new Conversion(
                new Id($input['id']),
                $campaignId,
                new LimitType($input['limit_type']),
                $input['is_repeatable']
            );

            $collection->add($conversion);
        }

        return $collection;
    }
}
