<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Domain\Exception\DomainRepositoryException;
use App\Domain\Model\BidStrategy;
use App\Domain\Model\BidStrategyCollection;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\IdCollection;
use App\Infrastructure\Repository\DoctrineBidStrategyRepository;
use Psr\Log\NullLogger;

final class DoctrineBidStrategyRepositoryTest extends RepositoryTestCase
{
    public function testRepository(): void
    {
        $repository = new DoctrineBidStrategyRepository($this->connection, new NullLogger());

        $this->assertEmpty($repository->fetchAll());

        $result = $repository->saveAll(new BidStrategyCollection());

        $this->assertEquals(0, $result);
        $this->assertEmpty($repository->fetchAll());

        $result = $repository->saveAll(
            new BidStrategyCollection(
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd01'), 'user:country:st', 0.99),
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd02'), 'user:country:us', 0.6)
            )
        );

        $this->assertEquals(2, $result);
        $this->assertCount(2, $repository->fetchAll());

        $result = $repository->saveAll(
            new BidStrategyCollection(
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd02'), 'user:country:us', 0.64),
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd03'), 'user:country:in', 0.4)
            )
        );

        $this->assertEquals(2, $result);
        $this->assertCount(3, $repository->fetchAll());
    }

    public function testDeleting(): void
    {
        $repository = new DoctrineBidStrategyRepository($this->connection, new NullLogger());

        $repository->saveAll(
            new BidStrategyCollection(
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd01'), 'user:country:st', 0.99),
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd02'), 'user:country:us', 0.6),
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd03'), 'user:country:in', 0.4)
            )
        );

        $list = array_filter(
            $repository->fetchAll()->toArray(),
            function (BidStrategy $bidStrategy) {
                return $bidStrategy->getDeletedAt() === null;
            }
        );
        $this->assertCount(3, $list);

        $this->assertEquals(1, $repository->deleteAll(new IdCollection(new Id('f1c567e1396b4cadb52223a51796fd02'))));

        $list = array_filter(
            $repository->fetchAll()->toArray(),
            function (BidStrategy $bidStrategy) {
                return $bidStrategy->getDeletedAt() === null;
            }
        );
        $this->assertCount(2, $list);

        $this->assertEquals(
            2,
            $repository->deleteAll(
                new IdCollection(new Id('f1c567e1396b4cadb52223a51796fd01'), new Id('f1c567e1396b4cadb52223a51796fd03'))
            )
        );

        $list = array_filter(
            $repository->fetchAll()->toArray(),
            function (BidStrategy $bidStrategy) {
                return $bidStrategy->getDeletedAt() === null;
            }
        );
        $this->assertEmpty($list);
    }

    public function testUpdate(): void
    {
        $repository = new DoctrineBidStrategyRepository($this->connection, new NullLogger());

        $repository->saveAll(
            new BidStrategyCollection(
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd01'), 'user:country:st', 0.99)
            )
        );

        $this->assertEquals(0.99, $repository->fetchAll()->first()->getRank());

        $repository->saveAll(
            new BidStrategyCollection(
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd01'), 'user:country:st', 0.88)
            )
        );

        $this->assertEquals(0.88, $repository->fetchAll()->first()->getRank());
    }

    public function testOverwrite(): void
    {
        $repository = new DoctrineBidStrategyRepository($this->connection, new NullLogger());

        $repository->saveAll(
            new BidStrategyCollection(
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd01'), 'user:country:st', 0.99),
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd01'), 'user:country:us', 0.6)
            )
        );

        $this->assertCount(2, $repository->fetchAll());

        $repository->saveAll(
            new BidStrategyCollection(
                new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd01'), 'user:country:st', 0.99)
            )
        );

        $this->assertCount(1, $repository->fetchAll());
    }

    public function testFetchingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrineBidStrategyRepository($this->failedConnection(), new NullLogger());
        $repository->fetchAll();
    }

    public function testSavingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrineBidStrategyRepository($this->failedConnection(), new NullLogger());
        $repository->saveAll(new BidStrategyCollection(
            new BidStrategy(new Id('f1c567e1396b4cadb52223a51796fd01'), 'user:country:st', 0.99)
        ));
    }

    public function testDeletingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrineBidStrategyRepository($this->failedConnection(), new NullLogger());
        $repository->deleteAll(new IdCollection());
    }
}
