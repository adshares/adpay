<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Limit;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use Adshares\AdPay\Lib\DateTimeHelper;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class ConversionTest extends TestCase
{
    public function testInstanceOfConversion(): void
    {
        $conversionId = 'ffc567e1396b4cadb52223a51796fd02';
        $campaignId = '43c567e1396b4cadb52223a51796fd01';
        $deletedAt = '2019-01-01T12:00:00+00:00';

        $limitValue = 1000000;
        $limitType = LimitType::createInBudget();
        $cost = 40;

        $limit = new Limit($limitValue, $limitType, $cost);

        $conversion = new Conversion(new Id($conversionId), new Id($campaignId), $limit);

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertEquals($conversionId, $conversion->getId());
        $this->assertEquals($campaignId, $conversion->getCampaignId());
        $this->assertEquals($limit, $conversion->getLimit());
        $this->assertEquals($limitValue, $conversion->getLimitValue());
        $this->assertEquals($limitType, $conversion->getLimitType());
        $this->assertEquals($cost, $conversion->getCost());
        $this->assertFalse($conversion->isValueMutable());
        $this->assertFalse($conversion->isRepeatable());
        $this->assertNull($conversion->getDeletedAt());

        $conversion =
            new Conversion(
                new Id($conversionId),
                new Id($campaignId),
                $limit,
                true,
                true,
                DateTimeHelper::fromString($deletedAt)
            );

        $this->assertTrue($conversion->isValueMutable());
        $this->assertTrue($conversion->isRepeatable());
        $this->assertEquals($deletedAt, $conversion->getDeletedAt()->format(DateTimeInterface::ATOM));

        $conversion = new Conversion(new Id($conversionId), new Id($campaignId), $limit, true, false);

        $this->assertTrue($conversion->isValueMutable());
        $this->assertFalse($conversion->isRepeatable());

        $conversion = new Conversion(new Id($conversionId), new Id($campaignId), $limit, false, true);

        $this->assertFalse($conversion->isValueMutable());
        $this->assertTrue($conversion->isRepeatable());
    }
}
