<?php

declare(strict_types=1);

namespace App\Tests\Application\Command;

use App\Application\Command\BidStrategyDeleteCommand;
use App\Application\DTO\BidStrategyDeleteDTO;
use App\Domain\Repository\BidStrategyRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class BidStrategyDeleteCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $dto = new BidStrategyDeleteDTO(['bid_strategies' => []]);

        $repository = $this->createMock(BidStrategyRepository::class);
        $repository->expects($this->once())->method('deleteAll')->with($dto->getIds())->willReturn(100);

        /** @var BidStrategyRepository $repository */
        $command = new BidStrategyDeleteCommand($repository, new NullLogger());
        $this->assertEquals(100, $command->execute($dto));
    }
}
