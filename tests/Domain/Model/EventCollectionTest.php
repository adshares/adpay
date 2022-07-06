<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\Model\Event;
use App\Domain\Model\EventCollection;
use App\Domain\Model\Impression;
use App\Domain\Model\ImpressionCase;
use App\Domain\ValueObject\Context;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
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

        $this->assertCount(
            4,
            new EventCollection(EventType::createView(), null, null, $item1, $item2, $item3, $item4)
        );
    }

    public function testEmptyCollection(): void
    {
        $collection = new EventCollection(EventType::createView());

        $this->assertEquals(EventType::VIEW, $collection->getType());
        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
        $this->assertNull($collection->getTimeStart());
        $this->assertNull($collection->getTimeEnd());
    }

    public function testTimeInterval(): void
    {
        $timeStart = new DateTime('@123123123');
        $timeEnd = new DateTime('@123123133');

        $collection = new EventCollection(EventType::createView(), $timeStart, $timeEnd);
        $this->assertEquals(EventType::VIEW, $collection->getType());
        $this->assertEquals($timeStart, $collection->getTimeStart());
        $this->assertEquals($timeEnd, $collection->getTimeEnd());

        $collection = new EventCollection(EventType::createView(), $timeStart);
        $this->assertEquals(EventType::VIEW, $collection->getType());
        $this->assertEquals($timeStart, $collection->getTimeStart());
        $this->assertNull($collection->getTimeEnd());

        $collection = new EventCollection(EventType::createView(), null, $timeEnd);
        $this->assertEquals(EventType::VIEW, $collection->getType());
        $this->assertNull($collection->getTimeStart());
        $this->assertEquals($timeEnd, $collection->getTimeEnd());
    }

    public function testInvalidInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EventCollection(EventType::createView(), new DateTime('@123123123'), new DateTime('@123123113'));
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
            Event::class,
            [
                new Id('0000000000000000000000000000000' . (string)$id),
                EventType::createView(),
                new DateTime(),
                new ImpressionCase(
                    new Id('13c567e1396b4cadb52223a51796fd01'),
                    new DateTime('-1 second'),
                    new Id('23c567e1396b4cadb52223a51796fd01'),
                    new Id('33c567e1396b4cadb52223a51796fd01'),
                    new Id('43c567e1396b4cadb52223a51796fd01'),
                    new Id('53c567e1396b4cadb52223a51796fd01'),
                    new Id('63c567e1396b4cadb52223a51796fd01'),
                    new Impression(
                        new Id('a3c567e1396b4cadb52223a51796fd01'),
                        new Id('b3c567e1396b4cadb52223a51796fd01'),
                        new Id('c3c567e1396b4cadb52223a51796fd01'),
                        new Context(0.89, 0.99)
                    )
                ),
            ]
        );

        return $mock;
    }
}
