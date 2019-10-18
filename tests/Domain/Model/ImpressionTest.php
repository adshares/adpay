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
        $keywords = ['k' => 111];
        $context = ['a' => 123];
        $humanScore = 0.89;
        $pageRank = 0.99;

        $impression = new Impression(
            new Id($impressionId),
            new Id($trackingId),
            new Id($userId),
            new Context($humanScore, $pageRank, $keywords, $context)
        );

        $this->assertInstanceOf(Impression::class, $impression);
        $this->assertEquals($impressionId, $impression->getId());
        $this->assertEquals($trackingId, $impression->getTrackingId());
        $this->assertEquals($userId, $impression->getUserId());
        $this->assertEquals($keywords, $impression->getKeywords());
        $this->assertEquals($humanScore, $impression->getHumanScore());
        $this->assertEquals($pageRank, $impression->getPageRank());
        $this->assertEquals($context, $impression->getContext()->getData());
        $this->assertEquals($context, $impression->getContextData());
    }
}
