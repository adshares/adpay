<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Helper;

use Adshares\AdPay\UI\Helper\InfoHelper;
use PHPUnit\Framework\TestCase;

final class InfoHelperTest extends TestCase
{
    public function testFromatInfo(): void
    {
        $this->assertEquals("A=100\n", InfoHelper::formatTxt(['a' => 100]));
        $this->assertEquals("A_BC=100\n", InfoHelper::formatTxt(['aBc' => 100]));
        $this->assertEquals("A=1,2,3\n", InfoHelper::formatTxt(['a' => [1, 2, 3]]));
        $this->assertEquals("A=\"100 m2\"\n", InfoHelper::formatTxt(['a' => '100 m2']));
    }
}
