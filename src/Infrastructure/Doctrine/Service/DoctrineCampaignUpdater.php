<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Service;

use Adshares\AdPay\Application\Exception\UpdateDataException;
use Adshares\AdPay\Application\Service\CampaignUpdater;
use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\BannerMapper;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\CampaignMapper;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\ConversionMapper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

class DoctrineCampaignUpdater extends DoctrineModelUpdater implements CampaignUpdater
{
    public function update(CampaignCollection $campaigns): int
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

    public function delete(IdCollection $ids): int
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
