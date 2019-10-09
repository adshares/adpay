<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\ValueObject\Context;
use PHPUnit\Framework\TestCase;

final class ContextTest extends TestCase
{
    public function testInstanceOfContext(): void
    {
        $aa = ['a' => 1];
        $bb = ['b' => 2];
        $cc = ['c' => 3];

        $data = [
            'aa' => $aa,
            'bb' => $bb,
            'cc' => $cc,
        ];

        $context = new Context($data);

        $this->assertInstanceOf(Context::class, $context);
        $this->assertEquals($data, $context->all());
        $this->assertEquals($aa, $context->get('aa'));
        $this->assertEquals($bb, $context->get('bb'));
        $this->assertEquals($cc, $context->get('cc'));
        $this->assertNull($context->get('dd'));
        $this->assertEquals(1, $context->get('aa', 'a'));
        $this->assertNull($context->get('aa', 'b'));
        $this->assertNull($context->get('aa', 'a', 'x'));
    }
}
