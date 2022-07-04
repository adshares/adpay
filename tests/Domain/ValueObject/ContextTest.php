<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\ValueObject\Context;
use PHPUnit\Framework\TestCase;

final class ContextTest extends TestCase
{
    public function testInstanceOfContext(): void
    {
        $k1 = ['kk1' => 'ka'];
        $k2 = ['kk2' => 'kb'];
        $k3 = ['kk3' => 'kc'];

        $aa = ['a' => 1];
        $bb = ['b' => 2];
        $cc = ['c' => 3];

        $humanScore = 0.89;
        $pageRank = 0.99;

        $keywords = [
            'k1' => $k1,
            'k2' => $k2,
            'k3' => $k3,
        ];

        $data = [
            'aa' => $aa,
            'bb' => $bb,
            'cc' => $cc,
        ];

        $context = new Context($humanScore, $pageRank, $keywords, $data);

        $this->assertInstanceOf(Context::class, $context);
        $this->assertEquals($humanScore, $context->getHumanScore());
        $this->assertEquals($pageRank, $context->getPageRank());
        $this->assertEquals($keywords, $context->getKeywords());
        $this->assertEquals($data, $context->getData());
        $this->assertEquals($aa, $context->get('aa'));
        $this->assertEquals($bb, $context->get('bb'));
        $this->assertEquals($cc, $context->get('cc'));
        $this->assertNull($context->get('dd'));
        $this->assertEquals(1, $context->get('aa', 'a'));
        $this->assertNull($context->get('aa', 'b'));
        $this->assertNull($context->get('aa', 'a', 'x'));
    }

    public function testCpaOnly(): void
    {
        $context = new Context(0.89, -1, [], []);
        $this->assertEquals(-1, $context->getPageRank());
    }

    public function testTooLowHumanScore(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Context(-0.88, 1.0);
    }

    public function testTooHightHumanScore(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Context(1.88, 1.0);
    }

    public function testTooLowPageRank(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Context(1.0, -0.88);
    }

    public function testTooHightPageRank(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Context(1.0, 1.88);
    }
}
