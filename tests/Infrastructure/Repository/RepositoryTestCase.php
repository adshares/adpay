<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Infrastructure\Repository;

use Doctrine\DBAL\Connection;
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
}
