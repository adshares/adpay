<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use Adshares\AdPay\Infrastructure\Repository\DoctrinePaymentReportRepository;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class PaymentsCalculateCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $this->setUpReports(1571828400);
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('ops:payments:calculate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
            ]
        );

        $this->assertEquals(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Calculating report #1571828400', $output);
        $this->assertStringContainsString('0 payments calculated', $output);
    }

    /**
     * @depends testExecute
     */
    public function testEmptyExecute(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('ops:payments:calculate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
            ]
        );

        $this->assertEquals(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Found 0 complete reports', $output);
    }

    /**
     * @depends testEmptyExecute
     */
    public function testExecuteWithTimestamp(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('ops:payments:calculate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'date' => '1571844764',
            ]
        );

        $this->assertEquals(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            'Calculating report #1571842800',
            $output
        );
        $this->assertStringContainsString('Report #1571842800 is not complete yet', $output);
    }

    /**
     * @depends testExecuteWithTimestamp
     */
    public function testExecuteWithDate(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('ops:payments:calculate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'date' => '2019-10-23 11:13:45',
            ]
        );

        $this->assertEquals(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            'Calculating report #1571828400',
            $output
        );
        $this->assertStringContainsString('Report #1571828400 is not complete yet', $output);
    }

    /**
     * @depends testExecuteWithDate
     */
    public function testForceExecute(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('ops:payments:calculate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'date' => '1571244764',
                '--force' => true,
            ]
        );

        $this->assertEquals(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            'Calculating report #1571241600',
            $output
        );
        $this->assertStringContainsString('0 payments calculated', $output);
    }

    /**
     * @depends testForceExecute
     */
    public function testInvalidDate(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('ops:payments:calculate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'date' => 'invalid_date',
            ]
        );

        $this->assertEquals(1, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Failed to parse time string', $output);
    }

    /**
     * @depends testInvalidDate
     * @runInSeparateProcess
     */
    public function testLockExecute(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('ops:payments:calculate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
            ]
        );

        $this->assertEquals(1, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('The command is already running in another process', $output);
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
