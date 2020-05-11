<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\Command;

use Adshares\AdPay\Application\Command\BidStrategyUpdateCommand;
use Adshares\AdPay\Application\DTO\BidStrategyUpdateDTO;
use Adshares\AdPay\Domain\Repository\BidStrategyRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class BidStrategyUpdateCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $dto = new BidStrategyUpdateDTO(['bid_strategies' => []]);

        $repository = $this->createMock(BidStrategyRepository::class);
        $repository->expects($this->once())->method('saveAll')->with($dto->getBidStrategies())->willReturn(100);

        /** @var BidStrategyRepository $repository */
        $command = new BidStrategyUpdateCommand($repository, new NullLogger());
        $this->assertEquals(100, $command->execute($dto));
    }
}
