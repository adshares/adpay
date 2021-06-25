<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\UI\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends KernelTestCase
{
    protected static $command;

    protected function executeCommand(
        array $options = [],
        int $expectedStatus = 0,
        string ...$expectedOutput
    ): CommandTester {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find(static::$command);
        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge(['command' => $command->getName()], $options));

        $this->assertEquals($expectedStatus, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();
        foreach ($expectedOutput as $output) {
            $this->assertStringContainsString($output, $display);
        }

        return $commandTester;
    }
}
