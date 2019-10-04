<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\BannerType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Size;
use PHPUnit\Framework\TestCase;

final class BannerCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $banner1 = self::createBanner(1, 1);
        $banner2 = self::createBanner(1, 2);
        $banner3 = self::createBanner(2, 3);
        $banner4 = self::createBanner(2, 4);

        $this->assertCount(4, new BannerCollection($banner1, $banner2, $banner3, $banner4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new BannerCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createBanner(int $campaignId, int $bannerId): Banner
    {
        return new Banner(
            new Id('0000000000000000000000000000000'.(string)$campaignId),
            new Id('0000000000000000000000000000000'.(string)$bannerId),
            new Size(100, 100),
            BannerType::createImage()
        );
    }
}
