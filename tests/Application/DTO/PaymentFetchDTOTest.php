<?php

declare(strict_types=1);

namespace App\Tests\Application\DTO;

use App\Application\DTO\PaymentFetchDTO;
use PHPUnit\Framework\TestCase;

final class PaymentFetchDTOTest extends TestCase
{
    public function testCalculated(): void
    {
        $dto = new PaymentFetchDTO(false, []);
        $this->assertFalse($dto->isCalculated());

        $dto = new PaymentFetchDTO(true, []);
        $this->assertTrue($dto->isCalculated());
    }

    public function testEmptyPayments(): void
    {
        $dto = new PaymentFetchDTO(false, []);
        $this->assertEmpty($dto->getPayments());
    }

    public function testPayments(): void
    {
        $dto = new PaymentFetchDTO(false, [self::payment(1), self::payment(2),]);
        $this->assertCount(2, $dto->getPayments());
        $this->assertEquals(
            [
                [
                    'event_id' => 'aac567e1396b4cadb52223a51796fdb1',
                    'event_type' => 'view',
                    'status' => 1,
                    'value' => 10000,
                ],
                [
                    'event_id' => 'aac567e1396b4cadb52223a51796fdb2',
                    'event_type' => 'view',
                    'status' => 1,
                    'value' => 10000,
                ],
            ],
            $dto->getPayments()
        );
    }

    private static function payment(int $id): array
    {
        return [
            'id' => $id,
            'report_id' => 123,
            'event_id' => 'aac567e1396b4cadb52223a51796fdb' . $id,
            'event_type' => 'view',
            'status' => 1,
            'value' => 10000,
        ];
    }
}
