<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\Command;

use Adshares\AdPay\Application\Command\EventDeleteCommand;
use Adshares\AdPay\Domain\Repository\EventRepository;
use Adshares\AdPay\Domain\ValueObject\EventType;
use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class EventDeleteCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $date = new DateTime('2019-01-01 12:00:00');

        $repository = $this->createMock(EventRepository::class);
        $repository->expects($this->exactly(3))->method('deleteByTime')->withConsecutive(
            [EventType::createView(), null, $date],
            [EventType::createClick(), null, $date],
            [EventType::createConversion(), null, $date]
        )->willReturnOnConsecutiveCalls(10, 20, 30);

        /** @var EventRepository $repository */
        $command = new EventDeleteCommand($repository, new NullLogger());
        $this->assertEquals(60, $command->execute($date));
    }
}
