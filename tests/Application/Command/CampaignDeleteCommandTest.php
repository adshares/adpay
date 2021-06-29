<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Application\Command;

use Adshares\AdPay\Application\Command\CampaignDeleteCommand;
use Adshares\AdPay\Application\DTO\CampaignDeleteDTO;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class CampaignDeleteCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $dto = new CampaignDeleteDTO(['campaigns' => []]);

        $repository = $this->createMock(CampaignRepository::class);
        $repository->expects($this->once())->method('deleteAll')->with($dto->getIds())->willReturn(100);

        /** @var CampaignRepository $repository */
        $command = new CampaignDeleteCommand($repository, new NullLogger());
        $this->assertEquals(100, $command->execute($dto));
    }
}
