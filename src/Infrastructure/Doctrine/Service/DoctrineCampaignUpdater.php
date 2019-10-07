<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Service;

use Adshares\AdPay\Application\Exception\UpdateDataException;
use Adshares\AdPay\Application\Service\CampaignUpdater;
use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\BannerMapper;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\CampaignMapper;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\ConversionMapper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Psr\Log\LoggerInterface;

class DoctrineCampaignUpdater implements CampaignUpdater
{
    /*  @var Connection */
    private $db;

    /* @var LoggerInterface */
    private $logger;

    public function __construct(Connection $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function update(CampaignCollection $campaigns): void
    {
        try {
            foreach ($campaigns as $campaign) {
                /*  @var $campaign Campaign */
                $this->upsert(
                    'campaigns',
                    $campaign->getId(),
                    CampaignMapper::map($campaign),
                    CampaignMapper::types()
                );
                foreach ($campaign->getBanners() as $banner) {
                    /*  @var $banner Banner */
                    $this->upsert('banners', $banner->getId(), BannerMapper::map($banner), BannerMapper::types());
                }
                foreach ($campaign->getConversions() as $conversion) {
                    /*  @var $conversion Conversion */
                    $this->upsert(
                        'conversions',
                        $conversion->getId(),
                        ConversionMapper::map($conversion),
                        ConversionMapper::types()
                    );
                }
            }
        } catch (DBALException $exception) {
            throw new UpdateDataException($exception->getMessage());
        }
    }

    public function delete(IdCollection $ids): void
    {
        try {
            $this->db->executeQuery(
                'DELETE FROM campaigns WHERE id IN (?)',
                [$ids->toBinArray()],
                [Connection::PARAM_STR_ARRAY]
            );
        } catch (DBALException $exception) {
            throw new UpdateDataException($exception->getMessage());
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function upsert(string $table, Id $id, array $data, array $types = [])
    {
        if ($this->isModelExists($table, $id)) {
            $this->db->update($table, $data, ['id' => $id->toBin()], $types);
        } else {
            $this->db->insert($table, $data, $types);
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function isModelExists(string $table, Id $id)
    {
        return $this->db->fetchColumn(
            sprintf('SELECT id FROM %s WHERE id = ?', $table),
            [$id->toBin()],
            0,
            [Type::BINARY]
        ) !== false;
    }
}
