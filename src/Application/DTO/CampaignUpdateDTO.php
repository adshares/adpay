<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\BannerType;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Limit;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use Adshares\AdPay\Domain\ValueObject\Size;
use Adshares\AdPay\Lib\DateTimeHelper;
use Adshares\AdPay\Lib\Exception\DateTimeException;
use TypeError;

final class CampaignUpdateDTO
{
    private $campaigns;

    public function __construct(array $campaigns)
    {
        $this->validate($campaigns);

        $campaignCollection = new CampaignCollection();

        foreach ($campaigns as $campaign) {
            $campaignCollection->add($this->createCampaignModel($campaign));
        }

        $this->campaigns = $campaignCollection;
    }

    private function createCampaignModel(array $campaignData): Campaign
    {
        try {
            $campaignId = new Id($campaignData['id']);
            $advertiserId = new Id($campaignData['advertiser_id']);
            $banners = $this->prepareBannerCollection($campaignId, $campaignData['banners']);
            $filters = $this->prepareFilters($campaignData['filters'] ?? []);
            $conversions = $this->prepareConversionCollection($campaignId, $campaignData['conversions'] ?? []);
            $budget =
                new Budget($campaignData['budget'], $campaignData['max_cpc'] ?? null, $campaignData['max_cpm'] ?? null);

            return new Campaign(
                $campaignId,
                $advertiserId,
                DateTimeHelper::createFromTimestamp($campaignData['time_start']),
                isset($campaignData['time_end']) ? DateTimeHelper::createFromTimestamp($campaignData['time_end'])
                    : null,
                $budget,
                $banners,
                $filters,
                $conversions
            );
        } catch (InvalidArgumentException|DateTimeException|TypeError $exception) {
            throw new ValidationDtoException($exception->getMessage());
        }
    }

    private function validate(array $campaigns): void
    {
        foreach ($campaigns as $campaign) {
            if (!isset($campaign['id'])) {
                throw new ValidationDTOException('Field `id` is required.');
            }

            if (!isset($campaign['advertiser_id'])) {
                throw new ValidationDTOException('Field `advertiser_id` is required.');
            }

            if (!isset($campaign['time_start'])) {
                throw new ValidationDTOException('Field `time_start` is required.');
            }

            if (!isset($campaign['budget'])) {
                throw new ValidationDTOException('Field `budget` is required.');
            }

            if (!isset($campaign['banners']) || empty($campaign['banners'])) {
                throw new ValidationDTOException('Field `banners` is required.');
            }

            if (!is_array($campaign['banners'])) {
                throw new ValidationDTOException('Field `banners` must be an array.');
            }

            $this->validateBanners($campaign['banners']);

            if (isset($campaign['filters'])) {
                if (!is_array($campaign['filters'])) {
                    throw new ValidationDTOException('Field `filters` must be an array.');
                }

                $this->validateFilters($campaign['filters']);
            }

            if (isset($campaign['conversions'])) {
                if (!is_array($campaign['conversions'])) {
                    throw new ValidationDTOException('Field `conversions` must be an array.');
                }

                $this->validateConversions($campaign['conversions']);
            }
        }
    }

    private function validateBanners(array $banners): void
    {
        foreach ($banners as $banner) {
            if (!isset($banner['id'])) {
                throw new ValidationDTOException('Field `banners[][id]` is required.');
            }

            if (!isset($banner['size'])) {
                throw new ValidationDTOException('Field `banners[][size]` is required.');
            }

            if (!isset($banner['type'])) {
                throw new ValidationDTOException('Field `banners[][type]` is required.');
            }
        }
    }

    private function validateFilters(array $filters): void
    {
        if (isset($filters['require']) && !is_array($filters['require'])) {
            throw new ValidationDTOException('Field `filters[require]` must be an array.');
        }

        if (isset($filters['exclude']) && !is_array($filters['exclude'])) {
            throw new ValidationDTOException('Field `filters[exclude]` must be an array.');
        }
    }

    private function validateConversions(array $conversions): void
    {
        foreach ($conversions as $conversion) {
            if (!isset($conversion['id'])) {
                throw new ValidationDTOException('Field `conversions[][id]` is required.');
            }

            if (!isset($conversion['limit_type'])) {
                throw new ValidationDTOException('Field `conversions[][limit_type]` is required.');
            }

            if (!isset($conversion['cost'])) {
                throw new ValidationDTOException('Field `conversions[][cost]` is required.');
            }

            if (!isset($conversion['is_repeatable'])) {
                throw new ValidationDTOException('Field `conversions[][is_repeatable]` is required.');
            }

            if (!isset($conversion['value'])) {
                throw new ValidationDTOException('Field `conversions[][value]` is required.');
            }

            if (!isset($conversion['is_value_mutable'])) {
                throw new ValidationDTOException('Field `conversions[][is_value_mutable]` is required.');
            }
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
                Size::fromString($input['size']),
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
                new Limit($input['limit'] ?? null, new LimitType($input['limit_type']), $input['cost']),
                $input['value'],
                $input['is_value_mutable'],
                $input['is_repeatable']
            );

            $collection->add($conversion);
        }

        return $collection;
    }

    public function getCampaigns(): CampaignCollection
    {
        return $this->campaigns;
    }
}
