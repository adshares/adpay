<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;
use DateTime;

final class CampaignTest extends TestCase
{
    public function testInstanceOfCampaign(): void
    {
        $campaignId = '43c567e1396b4cadb52223a51796fd01';
        $campaign = new Campaign(
            new Id($campaignId),
            new DateTime(),
            new DateTime(),
            new BannerCollection(),
            [],
            [],
            new Budget(1000000)
        );

        $this->assertInstanceOf(Campaign::class, $campaign);
    }
}
