<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\ViewEvent;
use Adshares\AdPay\Domain\Model\ViewEventCollection;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class ViewEventCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $item1 = self::createViewEvent(1);
        $item2 = self::createViewEvent(2);
        $item3 = self::createViewEvent(3);
        $item4 = self::createViewEvent(4);

        $this->assertCount(4, new ViewEventCollection($item1, $item2, $item3, $item4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new ViewEventCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createViewEvent(int $id): ViewEvent
    {
        return new ViewEvent(
            new Id('0000000000000000000000000000000'.(string)$id)
        );
    }
}
