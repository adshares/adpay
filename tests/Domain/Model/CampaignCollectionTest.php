<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;
use DateTime;

final class CampaignCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $banner1 = self::createCampaign(1, 1);
        $banner2 = self::createCampaign(1, 2);
        $banner3 = self::createCampaign(2, 3);
        $banner4 = self::createCampaign(2, 4);

        $this->assertCount(4, new CampaignCollection($banner1, $banner2, $banner3, $banner4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new CampaignCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createCampaign(int $campaignId, int $bannerId): Campaign
    {
        return new Campaign(
            new Id('0000000000000000000000000000000' . (string)$campaignId),
            new Id('0000000000000000000000000000000' . (string)$bannerId),
            new DateTime(),
            new DateTime(),
            new Budget(100),
            new BannerCollection(),
            [],
            new ConversionCollection()
        );
    }
}
