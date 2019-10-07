<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Limit;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use PHPUnit\Framework\TestCase;

final class ConversionCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $conversion1 = self::createConversion(1, 1);
        $conversion2 = self::createConversion(1, 2);
        $conversion3 = self::createConversion(2, 3);
        $conversion4 = self::createConversion(2, 4);

        $this->assertCount(4, new ConversionCollection($conversion1, $conversion2, $conversion3, $conversion4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new ConversionCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createConversion(int $campaignId, int $bannerId): Conversion
    {
        return new Conversion(
            new Id('0000000000000000000000000000000'.(string)$campaignId),
            new Id('0000000000000000000000000000000'.(string)$bannerId),
            new Limit(100, LimitType::createInBudget()),
            100
        );
    }
}
