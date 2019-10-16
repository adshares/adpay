<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use Adshares\AdPay\Infrastructure\Mapper\BannerMapper;
use Adshares\AdPay\Infrastructure\Mapper\CampaignMapper;
use Adshares\AdPay\Infrastructure\Mapper\ConversionMapper;
use Doctrine\DBAL\DBALException;

final class DoctrineCampaignRepository extends DoctrineModelUpdater implements CampaignRepository
{
    public function saveAll(CampaignCollection $campaigns): int
    {
        $count = 0;
        try {
            $ids = new IdCollection();
            foreach ($campaigns as $campaign) {
                /*  @var $campaign Campaign */
                $ids->add($campaign->getId());
            }
            $this->softDelete(BannerMapper::table(), $ids->toBinArray(), 'campaign_id');
            $this->softDelete(ConversionMapper::table(), $ids->toBinArray(), 'campaign_id');

            foreach ($campaigns as $campaign) {
                /*  @var $campaign Campaign */
                $this->upsert(
                    CampaignMapper::table(),
                    $campaign->getId(),
                    CampaignMapper::map($campaign),
                    CampaignMapper::types()
                );
                foreach ($campaign->getBanners() as $banner) {
                    /*  @var $banner Banner */
                    $this->upsert(
                        BannerMapper::table(),
                        $banner->getId(),
                        BannerMapper::map($banner),
                        BannerMapper::types()
                    );
                }
                foreach ($campaign->getConversions() as $conversion) {
                    /*  @var $conversion Conversion */
                    $this->upsert(
                        ConversionMapper::table(),
                        $conversion->getId(),
                        ConversionMapper::map($conversion),
                        ConversionMapper::types()
                    );
                }
                ++$count;
            }
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        return $count;
    }

    public function deleteAll(IdCollection $ids): int
    {
        try {
            $this->softDelete(BannerMapper::table(), $ids->toBinArray(), 'campaign_id');
            $this->softDelete(ConversionMapper::table(), $ids->toBinArray(), 'campaign_id');
            $result = $this->softDelete(CampaignMapper::table(), $ids->toBinArray());
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        return $result;
    }

    public function fetchAll(): CampaignCollection
    {
        $query = 'SELECT * FROM %s WHERE deleted_at IS NULL OR deleted_at > NOW() - INTERVAL 32 DAY';

        try {
            $campaignRows = $this->db->fetchAll(sprintf($query, CampaignMapper::table()));
            $bannerRows = $this->db->fetchAll(sprintf($query, BannerMapper::table()));
            $conversionRows = $this->db->fetchAll(sprintf($query, ConversionMapper::table()));
        } catch (DBALException $exception) {
            throw new DomainRepositoryException($exception->getMessage());
        }

        $banners = [];
        $conversions = [];

        foreach ($bannerRows as $row) {
            $banners[$row['campaign_id']][] = BannerMapper::fill($row);
        }
        foreach ($conversionRows as $row) {
            $conversions[$row['campaign_id']][] = ConversionMapper::fill($row);
        }

        $campaigns = new CampaignCollection();
        foreach ($campaignRows as $row) {
            $campaigns->add(
                CampaignMapper::fill(
                    $row,
                    new BannerCollection(...$banners[$row['id']] ?? []),
                    new ConversionCollection(...$conversions[$row['id']] ?? [])
                )
            );
        }

        return $campaigns;
    }
}
