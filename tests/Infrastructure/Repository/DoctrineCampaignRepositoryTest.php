<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\ValueObject\BannerType;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use Adshares\AdPay\Infrastructure\Repository\DoctrineCampaignRepository;
use DateTime;
use Psr\Log\NullLogger;

final class DoctrineCampaignRepositoryTest extends RepositoryTestCase
{
    public function testRepository(): void
    {
        $repository = new DoctrineCampaignRepository($this->connection, new NullLogger());

        $this->assertEmpty($repository->fetchAll());

        $repository->saveAll(
            new CampaignCollection(
                self::campaign('f1c567e1396b4cadb52223a51796fd01', new BannerCollection(), new ConversionCollection()),
                self::campaign('f1c567e1396b4cadb52223a51796fd02', new BannerCollection(), new ConversionCollection())
            )
        );

        $this->assertCount(2, $repository->fetchAll());

        $repository->saveAll(
            new CampaignCollection(
                self::campaign('f1c567e1396b4cadb52223a51796fd02', new BannerCollection(), new ConversionCollection()),
                self::campaign('f1c567e1396b4cadb52223a51796fd03', new BannerCollection(), new ConversionCollection())
            )
        );

        $this->assertCount(3, $repository->fetchAll());
    }

    public function testDeleting(): void
    {
        $repository = new DoctrineCampaignRepository($this->connection, new NullLogger());

        $repository->saveAll(
            new CampaignCollection(
                self::campaign('f1c567e1396b4cadb52223a51796fd01', new BannerCollection(), new ConversionCollection()),
                self::campaign('f1c567e1396b4cadb52223a51796fd02', new BannerCollection(), new ConversionCollection()),
                self::campaign('f1c567e1396b4cadb52223a51796fd03', new BannerCollection(), new ConversionCollection())
            )
        );

        $list = array_filter(
            $repository->fetchAll()->toArray(),
            function (Campaign $campaign) {
                return $campaign->getDeletedAt() === null;
            }
        );
        $this->assertCount(3, $list);

        $this->assertEquals(1, $repository->deleteAll(new IdCollection(new Id('f1c567e1396b4cadb52223a51796fd02'))));

        $list = array_filter(
            $repository->fetchAll()->toArray(),
            function (Campaign $campaign) {
                return $campaign->getDeletedAt() === null;
            }
        );
        $this->assertCount(2, $list);

        $this->assertEquals(
            2,
            $repository->deleteAll(
                new IdCollection(new Id('f1c567e1396b4cadb52223a51796fd01'), new Id('f1c567e1396b4cadb52223a51796fd03'))
            )
        );

        $list = array_filter(
            $repository->fetchAll()->toArray(),
            function (Campaign $campaign) {
                return $campaign->getDeletedAt() === null;
            }
        );
        $this->assertEmpty($list);
    }

    public function testDeletingBanners(): void
    {
        $repository = new DoctrineCampaignRepository($this->connection, new NullLogger());

        $repository->saveAll(
            new CampaignCollection(
                self::campaign('f1c567e1396b4cadb52223a51796fd01', new BannerCollection(), new ConversionCollection())
            )
        );

        $list = array_filter(
            $repository->fetchAll()->first()->getBanners()->toArray(),
            function (Banner $banner) {
                return $banner->getDeletedAt() === null;
            }
        );
        $this->assertEmpty($list);

        $repository->saveAll(
            new CampaignCollection(
                self::campaign(
                    'f1c567e1396b4cadb52223a51796fd01',
                    new BannerCollection(
                        self::banner('e1c567e1396b4cadb52223a51796fd01', 'f1c567e1396b4cadb52223a51796fd01'),
                        self::banner('e1c567e1396b4cadb52223a51796fd02', 'f1c567e1396b4cadb52223a51796fd01')
                    ),
                    new ConversionCollection()
                )
            )
        );

        $list = array_filter(
            $repository->fetchAll()->first()->getBanners()->toArray(),
            function (Banner $banner) {
                return $banner->getDeletedAt() === null;
            }
        );
        $this->assertCount(2, $list);

        $repository->saveAll(
            new CampaignCollection(
                self::campaign(
                    'f1c567e1396b4cadb52223a51796fd01',
                    new BannerCollection(
                        self::banner('e1c567e1396b4cadb52223a51796fd01', 'f1c567e1396b4cadb52223a51796fd01')
                    ),
                    new ConversionCollection()
                )
            )
        );

        $list = array_filter(
            $repository->fetchAll()->first()->getBanners()->toArray(),
            function (Banner $banner) {
                return $banner->getDeletedAt() === null;
            }
        );
        $this->assertCount(1, $list);
    }

    public function testDeletingConversions(): void
    {
        $repository = new DoctrineCampaignRepository($this->connection, new NullLogger());

        $repository->saveAll(
            new CampaignCollection(
                self::campaign('f1c567e1396b4cadb52223a51796fd01', new BannerCollection(), new ConversionCollection())
            )
        );

        $list = array_filter(
            $repository->fetchAll()->first()->getConversions()->toArray(),
            function (Conversion $conversion) {
                return $conversion->getDeletedAt() === null;
            }
        );
        $this->assertEmpty($list);

        $repository->saveAll(
            new CampaignCollection(
                self::campaign(
                    'f1c567e1396b4cadb52223a51796fd01',
                    new BannerCollection(),
                    new ConversionCollection(
                        self::conversion('e1c567e1396b4cadb52223a51796fd01', 'f1c567e1396b4cadb52223a51796fd01'),
                        self::conversion('e1c567e1396b4cadb52223a51796fd02', 'f1c567e1396b4cadb52223a51796fd01')
                    )
                )
            )
        );

        $list = array_filter(
            $repository->fetchAll()->first()->getConversions()->toArray(),
            function (Conversion $conversion) {
                return $conversion->getDeletedAt() === null;
            }
        );
        $this->assertCount(2, $list);

        $repository->saveAll(
            new CampaignCollection(
                self::campaign(
                    'f1c567e1396b4cadb52223a51796fd01',
                    new BannerCollection(),
                    new ConversionCollection(
                        self::conversion('e1c567e1396b4cadb52223a51796fd01', 'f1c567e1396b4cadb52223a51796fd01')
                    )
                )
            )
        );

        $list = array_filter(
            $repository->fetchAll()->first()->getConversions()->toArray(),
            function (Conversion $conversion) {
                return $conversion->getDeletedAt() === null;
            }
        );
        $this->assertCount(1, $list);
    }

    public function testSavingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrineCampaignRepository($this->failedConnection(), new NullLogger());
        $repository->saveAll(new CampaignCollection());
    }

    public function testFetchingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrineCampaignRepository($this->failedConnection(), new NullLogger());
        $repository->fetchAll();
    }

    public function testDeletingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrineCampaignRepository($this->failedConnection(), new NullLogger());
        $repository->deleteAll(new IdCollection());
    }

    private static function banner(string $id, string $campaignId): Banner
    {
        return new Banner(
            new Id($id),
            new Id($campaignId),
            '100x100',
            BannerType::createImage()
        );
    }

    private static function conversion(string $id, string $campaignId): Conversion
    {
        return new Conversion(
            new Id($id),
            new Id($campaignId),
            LimitType::createInBudget()
        );
    }

    private static function campaign(string $id, BannerCollection $banners, ConversionCollection $conversions): Campaign
    {
        return new Campaign(
            new Id($id),
            new Id('f2c567e1396b4cadb52223a51796fd01'),
            new DateTime(),
            null,
            new Budget(10000),
            $banners,
            [],
            $conversions
        );
    }
}
