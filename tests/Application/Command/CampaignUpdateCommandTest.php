<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Application\Command;

use Adshares\AdPay\Application\Command\CampaignUpdateCommand;
use Adshares\AdPay\Application\DTO\CampaignUpdateDTO;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class CampaignUpdateCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $dto = new CampaignUpdateDTO(['campaigns' => []]);

        $repository = $this->createMock(CampaignRepository::class);
        $repository->expects($this->once())->method('saveAll')->with($dto->getCampaigns())->willReturn(100);

        /** @var CampaignRepository $repository */
        $command = new CampaignUpdateCommand($repository, new NullLogger());
        $this->assertEquals(100, $command->execute($dto));
    }
}
