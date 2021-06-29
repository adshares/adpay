<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Exception\InvalidDataException;
use Adshares\AdPay\Domain\Model\ClickEvent;
use Adshares\AdPay\Domain\Model\ConversionEvent;
use Adshares\AdPay\Domain\Model\EventCollection;
use Adshares\AdPay\Domain\Model\Impression;
use Adshares\AdPay\Domain\Model\ImpressionCase;
use Adshares\AdPay\Domain\Model\ViewEvent;
use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Infrastructure\Repository\DoctrineEventRepository;
use Adshares\AdPay\Lib\DateTimeHelper;
use DateTime;
use Psr\Log\NullLogger;

final class DoctrineEventRepositoryTest extends RepositoryTestCase
{
    public function testRepository(): void
    {
        $timestamp = 1571838426;

        $events1 = new EventCollection(EventType::createView());
        $events1->add(self::viewEvent($timestamp - 100, 1));
        $events1->add(self::viewEvent($timestamp - 90, 2));
        $events1->add(self::viewEvent($timestamp - 80, 3));
        $events1->add(self::viewEvent($timestamp - 60, 4));
        $events1->add(self::viewEvent($timestamp - 50, 5));

        $events2 = new EventCollection(EventType::createClick());
        $events2->add(self::clickEvent($timestamp - 50, 1));
        $events2->add(self::clickEvent($timestamp - 40, 2));
        $events2->add(self::clickEvent($timestamp - 20, 3));

        $events3 = new EventCollection(EventType::createConversion());
        $events3->add(self::conversionEvent($timestamp - 20, 1));

        $repository = new DoctrineEventRepository($this->connection, new NullLogger());
        $repository->saveAll($events1);
        $repository->saveAll($events2);
        $repository->saveAll($events3);

        $this->assertCount(9, self::iterableToArray($repository->fetchByTime()));
        $this->assertCount(
            5,
            self::iterableToArray($repository->fetchByTime(DateTimeHelper::fromTimestamp($timestamp - 50)))
        );
        $this->assertCount(
            4,
            self::iterableToArray($repository->fetchByTime(null, DateTimeHelper::fromTimestamp($timestamp - 60)))
        );
        $this->assertCount(
            4,
            $repository->fetchByTime(
                DateTimeHelper::fromTimestamp($timestamp - 70),
                DateTimeHelper::fromTimestamp($timestamp - 30)
            )
        );
        $this->assertEmpty(
            self::iterableToArray($repository->fetchByTime(DateTimeHelper::fromTimestamp($timestamp - 10)))
        );
        $this->assertEmpty(
            self::iterableToArray($repository->fetchByTime(null, DateTimeHelper::fromTimestamp($timestamp - 110)))
        );
        $this->assertEmpty(
            self::iterableToArray(
                $repository->fetchByTime(
                    DateTimeHelper::fromTimestamp($timestamp - 75),
                    DateTimeHelper::fromTimestamp($timestamp - 70)
                )
            )
        );
    }

    public function testViewEvent(): void
    {
        $timestamp = 1571838426;
        $events = new EventCollection(EventType::createView());
        $events->add(self::viewEvent($timestamp, 1));
        $repository = new DoctrineEventRepository($this->connection, new NullLogger());
        $repository->saveAll($events);

        $events = self::iterableToArray($repository->fetchByTime());
        $event = array_pop($events);

        $this->assertEquals(EventType::VIEW, $event['type']);
        $this->assertEquals('f1c567e1396b4cadb52223a51796fd01', $event['id']);
        $this->assertEquals('2019-10-23 13:47:06', $event['time']);
        $this->assertEquals('13c567e1396b4cadb52223a51796fd01', $event['case_id']);
        $this->assertEquals('2019-10-23 13:47:06', $event['case_time']);
        $this->assertEquals('23c567e1396b4cadb52223a51796fd01', $event['publisher_id']);
        $this->assertEquals('33c567e1396b4cadb52223a51796fd01', $event['zone_id']);
        $this->assertEquals('43c567e1396b4cadb52223a51796fd01', $event['advertiser_id']);
        $this->assertEquals('53c567e1396b4cadb52223a51796fd01', $event['campaign_id']);
        $this->assertEquals('63c567e1396b4cadb52223a51796fd01', $event['banner_id']);
        $this->assertEquals('73c567e1396b4cadb52223a51796fd01', $event['impression_id']);
        $this->assertEquals('83c567e1396b4cadb52223a51796fd01', $event['tracking_id']);
        $this->assertEquals('93c567e1396b4cadb52223a51796fd01', $event['user_id']);
        $this->assertEquals(0.98, $event['human_score']);
        $this->assertEquals(0.74, $event['page_rank']);
        $this->assertEquals(['a' => 'aaa'], $event['keywords']);
        $this->assertEquals(['b' => 'bbb'], $event['context']);
    }

    public function testClickEvent(): void
    {
        $timestamp = 1571838426;
        $events = new EventCollection(EventType::createClick());
        $events->add(self::clickEvent($timestamp, 1));
        $repository = new DoctrineEventRepository($this->connection, new NullLogger());
        $repository->saveAll($events);

        $events = self::iterableToArray($repository->fetchByTime());
        $event = array_pop($events);

        $this->assertEquals(EventType::CLICK, $event['type']);
        $this->assertEquals('f1c567e1396b4cadb52223a51796fd01', $event['id']);
        $this->assertEquals('2019-10-23 13:47:06', $event['time']);
        $this->assertEquals('13c567e1396b4cadb52223a51796fd01', $event['case_id']);
        $this->assertEquals('2019-10-23 13:47:05', $event['case_time']);
        $this->assertEquals('23c567e1396b4cadb52223a51796fd01', $event['publisher_id']);
        $this->assertEquals('33c567e1396b4cadb52223a51796fd01', $event['zone_id']);
        $this->assertEquals('43c567e1396b4cadb52223a51796fd01', $event['advertiser_id']);
        $this->assertEquals('53c567e1396b4cadb52223a51796fd01', $event['campaign_id']);
        $this->assertEquals('63c567e1396b4cadb52223a51796fd01', $event['banner_id']);
        $this->assertEquals('73c567e1396b4cadb52223a51796fd01', $event['impression_id']);
        $this->assertEquals('83c567e1396b4cadb52223a51796fd01', $event['tracking_id']);
        $this->assertEquals('93c567e1396b4cadb52223a51796fd01', $event['user_id']);
        $this->assertEquals(0.98, $event['human_score']);
        $this->assertEquals(0.74, $event['page_rank']);
        $this->assertEquals(['a' => 'aaa'], $event['keywords']);
        $this->assertEquals(['b' => 'bbb'], $event['context']);
    }

    public function testConversionEvent(): void
    {
        $timestamp = 1571838426;
        $events = new EventCollection(EventType::createConversion());
        $events->add(self::conversionEvent($timestamp, 1));
        $repository = new DoctrineEventRepository($this->connection, new NullLogger());
        $repository->saveAll($events);

        $events = self::iterableToArray($repository->fetchByTime());
        $event = array_pop($events);

        $this->assertEquals(EventType::CONVERSION, $event['type']);
        $this->assertEquals('f1c567e1396b4cadb52223a51796fd01', $event['id']);
        $this->assertEquals('2019-10-23 13:47:06', $event['time']);
        $this->assertEquals('13c567e1396b4cadb52223a51796fd01', $event['case_id']);
        $this->assertEquals('2019-10-23 13:46:56', $event['case_time']);
        $this->assertEquals('23c567e1396b4cadb52223a51796fd01', $event['publisher_id']);
        $this->assertEquals('33c567e1396b4cadb52223a51796fd01', $event['zone_id']);
        $this->assertEquals('43c567e1396b4cadb52223a51796fd01', $event['advertiser_id']);
        $this->assertEquals('53c567e1396b4cadb52223a51796fd01', $event['campaign_id']);
        $this->assertEquals('63c567e1396b4cadb52223a51796fd01', $event['banner_id']);
        $this->assertEquals('73c567e1396b4cadb52223a51796fd01', $event['impression_id']);
        $this->assertEquals('83c567e1396b4cadb52223a51796fd01', $event['tracking_id']);
        $this->assertEquals('93c567e1396b4cadb52223a51796fd01', $event['user_id']);
        $this->assertEquals(0.98, $event['human_score']);
        $this->assertEquals(0.74, $event['page_rank']);
        $this->assertEquals(['a' => 'aaa'], $event['keywords']);
        $this->assertEquals(['b' => 'bbb'], $event['context']);
        $this->assertEquals('f2c567e1396b4cadb52223a51796fd01', $event['group_id']);
        $this->assertEquals('f3c567e1396b4cadb52223a51796fd01', $event['conversion_id']);
        $this->assertEquals(100, $event['conversion_value']);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $event['payment_status']);
    }

    public function testDuplicateKey(): void
    {
        $this->expectException(InvalidDataException::class);

        $events = new EventCollection(EventType::createView());
        $events->add(self::viewEvent(1571838426, 1));
        $events->add(self::viewEvent(1571838427, 1));

        $repository = new DoctrineEventRepository($this->connection, new NullLogger());
        $repository->saveAll($events);
    }

    public function testDeleting(): void
    {
        $timestamp = 1571838426;

        $events1 = new EventCollection(EventType::createView());
        $events1->add(self::viewEvent($timestamp - 100, 1));
        $events1->add(self::viewEvent($timestamp - 90, 2));
        $events1->add(self::viewEvent($timestamp - 80, 3));
        $events1->add(self::viewEvent($timestamp - 60, 4));
        $events1->add(self::viewEvent($timestamp - 50, 5));

        $repository = new DoctrineEventRepository($this->connection, new NullLogger());
        $repository->saveAll($events1);

        $this->assertEquals(
            0,
            $repository->deleteByTime(EventType::createView(), DateTimeHelper::fromTimestamp($timestamp))
        );
        $this->assertCount(5, self::iterableToArray($repository->fetchByTime()));
        $this->assertEquals(
            3,
            $repository->deleteByTime(
                EventType::createView(),
                DateTimeHelper::fromTimestamp($timestamp - 90),
                DateTimeHelper::fromTimestamp($timestamp - 60)
            )
        );
        $this->assertCount(2, self::iterableToArray($repository->fetchByTime()));
        $this->assertEquals(
            2,
            $repository->deleteByTime(EventType::createView(), DateTimeHelper::fromTimestamp($timestamp - 200))
        );
        $this->assertEmpty(self::iterableToArray($repository->fetchByTime()));
    }

    public function testSavingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrineEventRepository($this->failedConnection(), new NullLogger());
        $repository->saveAll(new EventCollection(EventType::createView()));
    }

    public function testFetchingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrineEventRepository($this->failedConnection(), new NullLogger());
        self::iterableToArray($repository->fetchByTime());
    }

    public function testDeletingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrineEventRepository($this->failedConnection(), new NullLogger());
        $repository->deleteByTime(EventType::createView(), new DateTime());
    }

    private static function impressionCase(int $timestamp): ImpressionCase
    {
        $impression = new Impression(
            new Id('73c567e1396b4cadb52223a51796fd01'),
            new Id('83c567e1396b4cadb52223a51796fd01'),
            new Id('93c567e1396b4cadb52223a51796fd01'),
            new Context(0.98, 0.74, ['a' => 'aaa'], ['b' => 'bbb'])
        );

        return new ImpressionCase(
            new Id('13c567e1396b4cadb52223a51796fd01'),
            DateTimeHelper::fromTimestamp($timestamp),
            new Id('23c567e1396b4cadb52223a51796fd01'),
            new Id('33c567e1396b4cadb52223a51796fd01'),
            new Id('43c567e1396b4cadb52223a51796fd01'),
            new Id('53c567e1396b4cadb52223a51796fd01'),
            new Id('63c567e1396b4cadb52223a51796fd01'),
            $impression
        );
    }

    private static function viewEvent(int $timestamp, int $id): ViewEvent
    {
        return new ViewEvent(
            new Id('f1c567e1396b4cadb52223a51796fd0' . $id),
            DateTimeHelper::fromTimestamp($timestamp),
            self::impressionCase($timestamp)
        );
    }

    private static function clickEvent(int $timestamp, int $id): ClickEvent
    {
        return new ClickEvent(
            new Id('f1c567e1396b4cadb52223a51796fd0' . $id),
            DateTimeHelper::fromTimestamp($timestamp),
            self::impressionCase($timestamp - 1)
        );
    }

    private static function conversionEvent(int $timestamp, int $id): ConversionEvent
    {
        return new ConversionEvent(
            new Id('f1c567e1396b4cadb52223a51796fd0' . $id),
            DateTimeHelper::fromTimestamp($timestamp),
            self::impressionCase($timestamp - 10),
            new Id('f2c567e1396b4cadb52223a51796fd01'),
            new Id('f3c567e1396b4cadb52223a51796fd01'),
            100,
            new PaymentStatus(PaymentStatus::HUMAN_SCORE_TOO_LOW)
        );
    }
}
