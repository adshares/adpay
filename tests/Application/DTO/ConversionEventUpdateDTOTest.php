<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\DTO;

use Adshares\AdPay\Application\DTO\ConversionEventUpdateDTO;
use Adshares\AdPay\Application\DTO\EventUpdateDTO;

final class ConversionEventUpdateDTOTest extends EventUpdateDTOTest
{
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
            [[static::simpleEvent(['value' => null])]],
            [[static::simpleEvent(['value' => 0])]],
            [[static::simpleEvent(['value' => 100])]],
        ];
    }

    protected static function invalidConversionDataProvider(): array
    {
        return [
            [[static::simpleEvent([], 'conversion_id')]],
            [[static::simpleEvent(['conversion_id' => null])]],
            [[static::simpleEvent(['conversion_id' => 0])]],
            [[static::simpleEvent(['conversion_id' => 'invalid_value'])]],
            [[static::simpleEvent(['value' => -100])]],
            [[static::simpleEvent(['value' => 'invalid_value'])]],
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
