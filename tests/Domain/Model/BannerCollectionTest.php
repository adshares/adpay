<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Model\Banner;
use App\Domain\Model\BannerCollection;
use App\Domain\ValueObject\BannerType;
use App\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class BannerCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $item1 = self::createBanner(1);
        $item2 = self::createBanner(2);
        $item3 = self::createBanner(3);
        $item4 = self::createBanner(4);

        $this->assertCount(4, new BannerCollection($item1, $item2, $item3, $item4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new BannerCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createBanner(int $id): Banner
    {
        return new Banner(
            new Id('0000000000000000000000000000000' . (string)$id),
            new Id('43c567e1396b4cadb52223a51796fd01'),
            '100x100',
            BannerType::createImage()
        );
    }
}
