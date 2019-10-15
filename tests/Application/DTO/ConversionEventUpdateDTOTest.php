<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\DTO;

use Adshares\AdPay\Application\DTO\ConversionEventUpdateDTO;
use Adshares\AdPay\Application\DTO\EventUpdateDTO;
use Adshares\AdPay\Domain\Model\ConversionEvent;

final class ConversionEventUpdateDTOTest extends EventUpdateDTOTest
{
    public function testConversionModel(): void
    {
        $input = static::simpleEvent(['conversion_value' => 100]);
        $dto = $this->createDTO(['time_start' => time() - 10, 'time_end' => time() - 1, 'events' => [$input]]);

        /* @var $event ConversionEvent */
        $event = $dto->getEvents()->first();

        $this->assertEquals($input['conversion_id'], $event->getConversionId());
        $this->assertEquals($input['conversion_value'], $event->getConversionValue());
    }

    protected function createDTO(array $data): EventUpdateDTO
    {
        return new ConversionEventUpdateDTO($data);
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
            [[static::simpleEvent(['conversion_value' => null])]],
            [[static::simpleEvent(['conversion_value' => 0])]],
            [[static::simpleEvent(['conversion_value' => 100])]],
        ];
    }

    protected static function invalidConversionDataProvider(): array
    {
        return [
            [[static::simpleEvent([], 'conversion_id')]],
            [[static::simpleEvent(['conversion_id' => null])]],
            [[static::simpleEvent(['conversion_id' => 0])]],
            [[static::simpleEvent(['conversion_id' => 'invalid_value'])]],
            [[static::simpleEvent(['conversion_value' => -100])]],
            [[static::simpleEvent(['conversion_value' => 'invalid_value'])]],
        ];
    }

    protected static function simpleEvent(array $mergeData = [], string $remove = null): array
    {
        $event = array_merge(
            parent::simpleEvent(),
            ['conversion_id' => '43c567e1396b4cadb52223a51796fd01'],
            $mergeData
        );

        if ($remove !== null) {
            unset($event[$remove]);
        }

        return $event;
    }
}
