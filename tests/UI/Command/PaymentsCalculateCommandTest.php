<?php

declare(strict_types=1);

namespace App\Tests\UI\Command;

use App\Domain\Model\PaymentReport;
use App\Domain\ValueObject\PaymentReportStatus;
use App\Infrastructure\Repository\DoctrinePaymentReportRepository;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;

final class PaymentsCalculateCommandTest extends CommandTestCase
{
    protected static $command = 'ops:payments:calculate';

    public function testExecute(): void
    {
        $this->setUpReports(1571824800);
        $this->executeCommand([], 0, 'Calculating report #1571824800', '0 payments calculated');
    }

    public function testEmptyExecute(): void
    {
        $this->executeCommand([], 0, 'Found 0 complete reports');
    }

    public function testExecuteWithTimestamp(): void
    {
        $this->executeCommand(
            ['date' => '1571844764'],
            0,
            'Calculating report #1571842800',
            'Report #1571842800 is not complete yet'
        );
    }

    public function testExecuteWithDate(): void
    {
        $this->executeCommand(
            ['date' => '2019-09-23 11:13:45'],
            0,
            'Calculating report #1569236400',
            'Report #1569236400 is not complete yet'
        );
    }

    public function testForceExecute(): void
    {
        $this->executeCommand(
            ['date' => '1571244764', '--force' => true],
            0,
            'Calculating report #1571241600',
            '0 payments calculated'
        );
    }

    public function testInvalidDate(): void
    {
        $this->executeCommand(['date' => 'invalid_date'], 1, 'Failed to parse time string');
    }

    public function testLock(): void
    {
        $store = SemaphoreStore::isSupported() ? new SemaphoreStore() : new FlockStore();
        $lock = (new LockFactory($store))->createLock(self::$command);
        self::assertTrue($lock->acquire());

        $this->executeCommand([], 1, 'The command is already running in another process.');

        $lock->release();
    }

    private function setUpReports(int ...$ids): void
    {
        $connection = self::bootKernel()->getContainer()->get('doctrine')->getConnection();
        $repository = new DoctrinePaymentReportRepository($connection, new NullLogger());
        foreach ($ids as $id) {
            $repository->save(new PaymentReport($id, PaymentReportStatus::createComplete()));
        }
    }
}
