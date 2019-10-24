<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class PaymentStatusTest extends TestCase
{
    public function testDefaultStatus(): void
    {
        $status = new PaymentStatus();

        $this->assertNull($status->getStatus());
        $this->assertFalse($status->isProcessed());
        $this->assertFalse($status->isAccepted());
        $this->assertFalse($status->isRejected());
        $this->assertEquals('unprocessed', $status->toString());
        $this->assertEquals('unprocessed', (string)$status);
    }

    public function testAcceptedStatus(): void
    {
        $status = new PaymentStatus(PaymentStatus::ACCEPTED);

        $this->assertEquals(PaymentStatus::ACCEPTED, $status->getStatus());
        $this->assertTrue($status->isProcessed());
        $this->assertTrue($status->isAccepted());
        $this->assertFalse($status->isRejected());
        $this->assertEquals('accepted', $status->toString());
    }

    public function testUnknownStatus(): void
    {
        $status = new PaymentStatus(PHP_INT_MAX);

        $this->assertTrue($status->isProcessed());
        $this->assertFalse($status->isAccepted());
        $this->assertTrue($status->isRejected());
        $this->assertEquals('rejected:unknown', $status->toString());
    }

    public function testInvalidStatus(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaymentStatus(-1);
    }

    /**
     * @dataProvider rejectedStatusProvider
     */
    public function testRejectedStatus(int $status, string $label): void
    {
        $status = new PaymentStatus($status);

        $this->assertTrue($status->isProcessed());
        $this->assertFalse($status->isAccepted());
        $this->assertTrue($status->isRejected());
        $this->assertEquals('rejected:'.$label, $status->toString());
    }

    public function rejectedStatusProvider(): array
    {
        return [
            [PaymentStatus::CAMPAIGN_NOT_FOUND, 'campaign_not_found'],
            [PaymentStatus::CAMPAIGN_OUTDATED, 'campaign_outdated'],
            [PaymentStatus::BANNER_NOT_FOUND, 'banner_not_found'],
            [PaymentStatus::INVALID_TARGETING, 'invalid_targeting'],
            [PaymentStatus::HUMAN_SCORE_TOO_LOW, 'human_score_too_low'],
            [PaymentStatus::CONVERSION_NOT_FOUND, 'conversion_not_found'],
        ];
    }
}
