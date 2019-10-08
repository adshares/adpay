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
        $item1 = self::createCampaign(1);
        $item2 = self::createCampaign(2);
        $item3 = self::createCampaign(3);
        $item4 = self::createCampaign(4);

        $this->assertCount(4, new CampaignCollection($item1, $item2, $item3, $item4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new CampaignCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createCampaign(int $id): Campaign
    {
        return new Campaign(
            new Id('0000000000000000000000000000000' . (string)$id),
            new Id('43c567e1396b4cadb52223a51796fd01'),
            new DateTime(),
            new DateTime(),
            new Budget(100),
            new BannerCollection(),
            [],
            new ConversionCollection()
        );
    }
}
