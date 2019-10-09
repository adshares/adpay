<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Impression;
use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class ImpressionTest extends TestCase
{
    public function testInstanceOfImpression(): void
    {
        $impressionId = '43c567e1396b4cadb52223a51796fd01';
        $trackingId = 'ffc567e1396b4cadb52223a51796fd02';
        $userId = 'aac567e1396b4cadb52223a51796fdbb';
        $context = ['a' => 123];
        $humanScore = 0.99;

        $impression = new Impression(
            new Id($impressionId),
            new Id($trackingId),
            new Id($userId),
            new Context($context),
            $humanScore
        );

        $this->assertInstanceOf(Impression::class, $impression);
        $this->assertEquals($impressionId, $impression->getId());
        $this->assertEquals($trackingId, $impression->getTrackingId());
        $this->assertEquals($userId, $impression->getUserId());
        $this->assertEquals($context, $impression->getContext()->all());
        $this->assertEquals($context, $impression->getContextData());
        $this->assertEquals($humanScore, $impression->getHumanScore());
    }
}
