<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Application\Command;

use Adshares\AdPay\Application\Command\BidStrategyDeleteCommand;
use Adshares\AdPay\Application\DTO\BidStrategyDeleteDTO;
use Adshares\AdPay\Domain\Repository\BidStrategyRepository;
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
