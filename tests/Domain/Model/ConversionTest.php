<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Limit;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use PHPUnit\Framework\TestCase;

final class ConversionTest extends TestCase
{
    public function testInstanceOfConversion(): void
    {
        $conversionId = 'ffc567e1396b4cadb52223a51796fd02';
        $campaignId = '43c567e1396b4cadb52223a51796fd01';
        $value = 25000;

        $limitValue = 1000000;
        $limitType = LimitType::createInBudget();
        $cost = 40;

        $limit = new Limit($limitValue, $limitType, $cost);

        $campaign = new Conversion(new Id($conversionId), new Id($campaignId), $limit, $value);

        $this->assertInstanceOf(Conversion::class, $campaign);
        $this->assertEquals($conversionId, $campaign->getId());
        $this->assertEquals($campaignId, $campaign->getCampaignId());
        $this->assertEquals($limit, $campaign->getLimit());
        $this->assertEquals($limitValue, $campaign->getLimitValue());
        $this->assertEquals($limitType, $campaign->getLimitType());
        $this->assertEquals($cost, $campaign->getCost());
        $this->assertEquals($value, $campaign->getValue());
        $this->assertFalse($campaign->isValueMutable());
        $this->assertFalse($campaign->isRepeatable());

        $campaign = new Conversion(new Id($conversionId), new Id($campaignId), $limit, $value, true, true);

        $this->assertTrue($campaign->isValueMutable());
        $this->assertTrue($campaign->isRepeatable());

        $campaign = new Conversion(new Id($conversionId), new Id($campaignId), $limit, $value, true, false);

        $this->assertTrue($campaign->isValueMutable());
        $this->assertFalse($campaign->isRepeatable());

        $campaign = new Conversion(new Id($conversionId), new Id($campaignId), $limit, $value, false, true);

        $this->assertFalse($campaign->isValueMutable());
        $this->assertTrue($campaign->isRepeatable());
    }

    public function testInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Conversion(
            new Id('43c567e1396b4cadb52223a51796fd01'),
            new Id('43c567e1396b4cadb52223a51796fd01'),
            new Limit(null, LimitType::createInBudget()),
            -100
        );
    }
}
