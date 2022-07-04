<?php

declare(strict_types=1);

namespace App\Tests\Application\Command;

use App\Application\Command\CampaignDeleteCommand;
use App\Application\DTO\CampaignDeleteDTO;
use App\Domain\Repository\CampaignRepository;
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
