<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Model\Banner;
use App\Domain\ValueObject\BannerType;
use App\Domain\ValueObject\Id;
use App\Lib\DateTimeHelper;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class BannerTest extends TestCase
{
    public function testInstanceOfBanner(): void
    {
        $campaignId = '43c567e1396b4cadb52223a51796fd01';
        $bannerId = 'ffc567e1396b4cadb52223a51796fd02';
        $size = '100x200';
        $deletedAt = '2019-01-01T12:00:00+00:00';

        $banner =
            new Banner(new Id($bannerId), new Id($campaignId), $size, BannerType::createImage());

        $this->assertInstanceOf(Banner::class, $banner);
        $this->assertEquals($bannerId, $banner->getId());
        $this->assertEquals($campaignId, $banner->getCampaignId());
        $this->assertEquals($size, $banner->getSize());
        $this->assertEquals(BannerType::IMAGE, $banner->getType());
        $this->assertNull($banner->getDeletedAt());

        $banner = new Banner(
            new Id($bannerId),
            new Id($campaignId),
            $size,
            BannerType::createImage(),
            DateTimeHelper::fromString($deletedAt)
        );

        $this->assertEquals($deletedAt, $banner->getDeletedAt()->format(DateTimeInterface::ATOM));
    }
}
