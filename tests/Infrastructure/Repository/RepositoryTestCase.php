<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Infrastructure\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class RepositoryTestCase extends KernelTestCase
{
    /** @var Connection */
    protected $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = self::bootKernel()->getContainer()->get('doctrine')->getConnection();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->close();
    }

    protected function failedConnection(): Connection
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('executeQuery')->willThrowException(new DBALException());
        $connection->method('executeUpdate')->willThrowException(new DBALException());
        $connection->method('fetchAssoc')->willThrowException(new DBALException());
        $connection->method('fetchColumn')->willThrowException(new DBALException());
        $connection->method('fetchAll')->willThrowException(new DBALException());

        return $connection;
    }

    protected static function iterableToArray(iterable $list): array
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = $item;
        }

        return $result;
    }
}
