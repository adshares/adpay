<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\ValueObject\BannerType;
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

    public function __construct(array $input)
    {
        $this->validate($input);

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
            $banners = $this->prepareBannerCollection($campaignId, $input['banners']);
            $filters = $this->prepareFilters($input['filters'] ?? []);
            $conversions = $this->prepareConversionCollection($campaignId, $input['conversions'] ?? []);
            $budget =
                new Budget($input['budget'], $input['max_cpc'] ?? null, $input['max_cpm'] ?? null);

            return new Campaign(
                $campaignId,
                $advertiserId,
                DateTimeHelper::createFromTimestamp($input['time_start']),
                isset($input['time_end']) ? DateTimeHelper::createFromTimestamp($input['time_end'])
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

    private function validate(array $input): void
    {
        if (!isset($input['campaigns'])) {
            throw new ValidationDTOException('Field `campaigns` is required.');
        }

        foreach ($input['campaigns'] as $campaign) {
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
        foreach ($banners as $input) {
            if (!isset($input['id'])) {
                throw new ValidationDTOException('Field `banners[][id]` is required.');
            }

            if (!isset($input['size'])) {
                throw new ValidationDTOException('Field `banners[][size]` is required.');
            }

            if (!isset($input['type'])) {
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
        foreach ($conversions as $input) {
            if (!isset($input['id'])) {
                throw new ValidationDTOException('Field `conversions[][id]` is required.');
            }

            if (!isset($input['limit_type'])) {
                throw new ValidationDTOException('Field `conversions[][limit_type]` is required.');
            }

            if (!isset($input['cost'])) {
                throw new ValidationDTOException('Field `conversions[][cost]` is required.');
            }

            if (!isset($input['is_repeatable'])) {
                throw new ValidationDTOException('Field `conversions[][is_repeatable]` is required.');
            }

            if (!isset($input['value'])) {
                throw new ValidationDTOException('Field `conversions[][value]` is required.');
            }

            if (!isset($input['is_value_mutable'])) {
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
