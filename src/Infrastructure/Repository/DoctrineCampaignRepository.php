<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\UpdateDataException;
use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use Adshares\AdPay\Infrastructure\Mapper\BannerMapper;
use Adshares\AdPay\Infrastructure\Mapper\CampaignMapper;
use Adshares\AdPay\Infrastructure\Mapper\ConversionMapper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

final class DoctrineCampaignRepository extends DoctrineModelUpdater implements CampaignRepository
{
    public function saveAll(CampaignCollection $campaigns): int
    {
        $count = 0;
        try {
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
            throw new UpdateDataException($exception->getMessage());
        }

        return $count;
    }

    public function deleteAll(IdCollection $ids): int
    {
        try {
            $result = $this->db->executeUpdate(
                'DELETE FROM campaigns WHERE id IN (?)',
                [$ids->toBinArray()],
                [Connection::PARAM_STR_ARRAY]
            );
        } catch (DBALException $exception) {
            throw new UpdateDataException($exception->getMessage());
        }

        return $result;
    }
}
