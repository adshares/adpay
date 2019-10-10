<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\Model\EventCollection;
use Adshares\AdPay\Domain\Model\Impression;
use Adshares\AdPay\Domain\Model\ImpressionCase;
use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use DateTime;
use PHPUnit\Framework\TestCase;

final class EventCollectionTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testMultiplyAdding(): void
    {
        $item1 = $this->createEvent(1);
        $item2 = $this->createEvent(2);
        $item3 = $this->createEvent(3);
        $item4 = $this->createEvent(4);

        $this->assertCount(4, new EventCollection($item1, $item2, $item3, $item4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new EventCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    /**
     * @param int $id
     *
     * @return Event
     * @throws \ReflectionException
     */
    private function createEvent(int $id): Event
    {
        /* @var $mock Event */
        $mock = $this->getMockForAbstractClass(
            'Adshares\AdPay\Domain\Model\Event',
            [
                new Id('0000000000000000000000000000000'.(string)$id),
                EventType::createView(),
                new DateTime(),
                new ImpressionCase(
                    new Id('13c567e1396b4cadb52223a51796fd01'),
                    new Id('23c567e1396b4cadb52223a51796fd01'),
                    new Id('33c567e1396b4cadb52223a51796fd01'),
                    new Id('43c567e1396b4cadb52223a51796fd01'),
                    new Id('53c567e1396b4cadb52223a51796fd01'),
                    new Id('63c567e1396b4cadb52223a51796fd01'),
                    new Impression(
                        new Id('a3c567e1396b4cadb52223a51796fd01'),
                        new Id('b3c567e1396b4cadb52223a51796fd01'),
                        new Id('c3c567e1396b4cadb52223a51796fd01'),
                        new Context(0.99)
                    )
                ),
            ]
        );

        return $mock;
    }
}
