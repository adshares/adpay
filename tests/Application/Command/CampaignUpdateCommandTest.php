<?php

declare(strict_types=1);

namespace App\Tests\Application\Command;

use App\Application\Command\CampaignUpdateCommand;
use App\Application\DTO\CampaignUpdateDTO;
use App\Domain\Repository\CampaignRepository;
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
