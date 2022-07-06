<?php

declare(strict_types=1);

namespace App\Tests\Application\DTO;

use App\Application\DTO\ConversionEventUpdateDTO;
use App\Application\DTO\EventUpdateDTO;
use App\Domain\Model\ConversionEvent;
use App\Domain\ValueObject\EventType;

final class ConversionEventUpdateDTOTest extends EventUpdateDTOTest
{
    protected function getEventType(): EventType
    {
        return EventType::createConversion();
    }

    protected function createDTO(array $data): EventUpdateDTO
    {
        return new ConversionEventUpdateDTO($data);
    }

    public function testConversionModel(): void
    {
        $input = static::simpleEvent(['payment_status' => 1]);
        $dto = $this->createDTO(['time_start' => time() - 500, 'time_end' => time() - 1, 'events' => [$input]]);

        /* @var $event ConversionEvent */
        $event = $dto->getEvents()->first();

        $this->assertEquals($input['conversion_id'], $event->getConversionId());
        $this->assertEquals($input['conversion_value'], $event->getConversionValue());
        $this->assertEquals($input['payment_status'], $event->getPaymentStatus()->getStatus());
    }

    public static function validDataProvider(): array
    {
        return array_merge(
            parent::validDataProvider(),
            static::validConversionDataProvider()
        );
    }

    public static function invalidDataProvider(): array
    {
        return array_merge(
            parent::invalidDataProvider(),
            static::invalidConversionDataProvider()
        );
    }

    protected static function validConversionDataProvider(): array
    {
        return [
            [[static::simpleEvent(['conversion_value' => 0])]],
            [[static::simpleEvent(['payment_status' => null])]],
            [[static::simpleEvent(['payment_status' => 0])]],
            [[static::simpleEvent(['payment_status' => 1])]],
        ];
    }

    protected static function invalidConversionDataProvider(): array
    {
        return [
            [[static::simpleEvent([], 'group_id')]],
            [[static::simpleEvent(['group_id' => null])]],
            [[static::simpleEvent(['group_id' => 0])]],
            [[static::simpleEvent(['group_id' => 'invalid_value'])]],
            [[static::simpleEvent([], 'conversion_id')]],
            [[static::simpleEvent(['conversion_id' => null])]],
            [[static::simpleEvent(['conversion_id' => 0])]],
            [[static::simpleEvent(['conversion_id' => 'invalid_value'])]],
            [[static::simpleEvent([], 'conversion_value')]],
            [[static::simpleEvent(['conversion_value' => null])]],
            [[static::simpleEvent(['conversion_value' => -100])]],
            [[static::simpleEvent(['conversion_value' => 'invalid_value'])]],
            [[static::simpleEvent(['payment_status' => -1])]],
            [[static::simpleEvent(['payment_status' => 'invalid_value'])]],
        ];
    }

    protected static function simpleEvent(array $mergeData = [], string $remove = null): array
    {
        $event = array_merge(
            parent::simpleEvent(),
            ['group_id' => '66c567e1396b4cadb52223a51796fd01'],
            ['conversion_id' => '43c567e1396b4cadb52223a51796fd01'],
            ['conversion_value' => 100],
            $mergeData
        );

        if ($remove !== null) {
            unset($event[$remove]);
        }

        return $event;
    }
}
