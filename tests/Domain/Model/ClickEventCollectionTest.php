<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\ClickEvent;
use Adshares\AdPay\Domain\Model\ClickEventCollection;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class ClickEventCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $item1 = self::createClickEvent(1);
        $item2 = self::createClickEvent(2);
        $item3 = self::createClickEvent(3);
        $item4 = self::createClickEvent(4);

        $this->assertCount(4, new ClickEventCollection($item1, $item2, $item3, $item4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new ClickEventCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createClickEvent(int $id): ClickEvent
    {
        return new ClickEvent(
            new Id('0000000000000000000000000000000'.(string)$id)
        );
    }
}
