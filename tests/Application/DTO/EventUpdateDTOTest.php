<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\DTO;

use Adshares\AdPay\Application\DTO\EventUpdateDTO;
use Adshares\AdPay\Application\Exception\ValidationException;
use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\ValueObject\EventType;
use PHPUnit\Framework\TestCase;

abstract class EventUpdateDTOTest extends TestCase
{
    abstract protected function getEventType(): EventType;

    abstract protected function createDTO(array $data): EventUpdateDTO;

    public function testEmptyInputData(): void
    {
        $this->expectException(ValidationException::class);

        $this->createDTO([]);
    }

    public function testInvalidInputData(): void
    {
        $this->expectException(ValidationException::class);

        $this->createDTO(['invalid' => []]);
    }

    public function testNoEventsInputData(): void
    {
        $this->expectException(ValidationException::class);

        $this->createDTO(
            [
                'time_start' => time() - 10,
                'time_end' => time() - 1,
            ]
        );
    }

    public function testValidTimespanData(): void
    {
        $time_start = time() - 10;
        $time_end = time() - 1;

        $dto = $this->createDTO(
            [
                'time_start' => $time_start,
                'time_end' => $time_end,
                'events' => [],
            ]
        );

        $this->assertEquals($time_start, $dto->getEvents()->getTimeStart()->getTimestamp());
        $this->assertEquals($time_end, $dto->getEvents()->getTimeEnd()->getTimestamp());

        $dto = $this->createDTO(
            [
                'time_start' => $time_start,
                'time_end' => $time_start,
                'events' => [],
            ]
        );

        $this->assertEquals($time_start, $dto->getEvents()->getTimeStart()->getTimestamp());
        $this->assertEquals($time_start, $dto->getEvents()->getTimeEnd()->getTimestamp());
    }

    /**
     * @dataProvider invalidTimespanDataProvider
     */
    public function testInvalidTimespanData($data): void
    {
        $this->expectException(ValidationException::class);

        $this->createDTO(array_merge(['events' => []], $data));
    }

    public function testEventTimeOutOfRangeLeft(): void
    {
        $this->expectException(ValidationException::class);

        $this->createDTO(
            [
                'time_start' => time() - 10,
                'time_end' => time() - 1,
                'events' => [
                    static::simpleEvent(['time' => time() - 15]),
                ],
            ]
        );
    }

    public function testEventTimeOutOfRangeRight(): void
    {
        $this->expectException(ValidationException::class);

        $this->createDTO(
            [
                'time_start' => time() - 10,
                'time_end' => time() - 5,
                'events' => [
                    static::simpleEvent(['time' => time() - 1]),
                ],
            ]
        );
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testValidData(array $data, int $count = 1): void
    {
        $dto = $this->createDTO(
            [
                'time_start' => time() - 10,
                'time_end' => time() - 1,
                'events' => $data,
            ]
        );

        $this->assertCount($count, $dto->getEvents());
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testInvalidData(array $data): void
    {
        $this->expectException(ValidationException::class);

        $this->createDTO(
            [
                'time_start' => time() - 10,
                'time_end' => time() - 1,
                'events' => $data,
            ]
        );
    }

    public function testModel(): void
    {
        $input = static::simpleEvent(
            ['zone_id' => 'aac567e1396b4cadb52223a51796fdbb', 'context' => ['a' => 1]]
        );
        $dto = $this->createDTO(['time_start' => time() - 10, 'time_end' => time() - 1, 'events' => [$input],]);

        /* @var $event Event */
        $event = $dto->getEvents()->first();

        $this->assertEquals($this->getEventType()->toString(), $event->getType()->toString());
        $this->assertEquals($input['id'], $event->getId());
        $this->assertEquals($input['time'], $event->getTime()->getTimestamp());
        $this->assertEquals($input['case_id'], $event->getCaseId());
        $this->assertEquals($input['publisher_id'], $event->getPublisherId());
        $this->assertEquals($input['zone_id'], $event->getZoneId());
        $this->assertEquals($input['advertiser_id'], $event->getAdvertiserId());
        $this->assertEquals($input['campaign_id'], $event->getCampaignId());
        $this->assertEquals($input['banner_id'], $event->getBannerId());
        $this->assertEquals($input['impression_id'], $event->getImpressionId());
        $this->assertEquals($input['tracking_id'], $event->getTrackingId());
        $this->assertEquals($input['user_id'], $event->getUserId());
        $this->assertEquals($input['context'], $event->getContextData());
        $this->assertEquals($input['human_score'], $event->getHumanScore());
    }

    public static function invalidTimespanDataProvider(): array
    {
        return [
            [[]],
            [['time_start' => time() - 1]],
            [['time_end' => time() - 1]],
            [['time_start' => 'invalid', 'time_end' => time() - 1]],
            [['time_start' => time() - 1, 'time_end' => 'invalid']],
            [['time_start' => time() - 1, 'time_end' => time() - 10]],
            [['time_start' => time() - 3000000, 'time_end' => time() - 1]],
            [['time_start' => time() - 10, 'time_end' => time() + 10]],
        ];
    }

    public static function validDataProvider(): array
    {
        return array_merge(
            static::validEventsDataProvider(),
            static::validCaseDataProvider(),
            static::validImpressionDataProvider()
        );
    }

    public static function invalidDataProvider(): array
    {
        return array_merge(
            static::invalidEventsDataProvider(),
            static::invalidCaseDataProvider(),
            static::invalidImpressionDataProvider()
        );
    }

    protected static function validEventsDataProvider(): array
    {
        return [
            [[], 0],
            [[static::simpleEvent()]],
            [[static::simpleEvent(), static::simpleEvent()], 2],
        ];
    }

    protected static function validCaseDataProvider(): array
    {
        return [
            [[static::simpleEvent(['zone_id' => null])]],
            [[static::simpleEvent(['zone_id' => 'aac567e1396b4cadb52223a51796fdbb'])]],
        ];
    }

    protected static function validImpressionDataProvider(): array
    {
        return [
            [[static::simpleEvent(['keywords' => null])]],
            [[static::simpleEvent(['keywords' => []])]],
            [[static::simpleEvent(['keywords' => ['k' => 333]])]],
            [[static::simpleEvent(['context' => null])]],
            [[static::simpleEvent(['context' => []])]],
            [[static::simpleEvent(['context' => ['a' => 123]])]],
            [[static::simpleEvent(['page_rank' => 0.0])]],
            [[static::simpleEvent(['page_rank' => 0.59])]],
            [[static::simpleEvent(['page_rank' => 1.0])]],
        ];
    }

    protected static function invalidEventsDataProvider(): array
    {
        return [
            [[static::simpleEvent([], 'id')]],
            [[static::simpleEvent(['id' => null])]],
            [[static::simpleEvent(['id' => 0])]],
            [[static::simpleEvent(['id' => 'invalid_value'])]],
            [[static::simpleEvent([], 'time')]],
            [[static::simpleEvent(['time' => null])]],
            [[static::simpleEvent(['time' => 0])]],
            [[static::simpleEvent(['time' => 'invalid_value'])]],
        ];
    }

    protected static function invalidCaseDataProvider(): array
    {
        return [
            [[static::simpleEvent([], 'case_id')]],
            [[static::simpleEvent(['case_id' => null])]],
            [[static::simpleEvent(['case_id' => 0])]],
            [[static::simpleEvent(['case_id' => 'invalid_value'])]],
            [[static::simpleEvent([], 'publisher_id')]],
            [[static::simpleEvent(['publisher_id' => null])]],
            [[static::simpleEvent(['publisher_id' => 0])]],
            [[static::simpleEvent(['publisher_id' => 'invalid_value'])]],
            [[static::simpleEvent(['zone_id' => 0])]],
            [[static::simpleEvent(['zone_id' => 'invalid_value'])]],
            [[static::simpleEvent([], 'advertiser_id')]],
            [[static::simpleEvent(['advertiser_id' => null])]],
            [[static::simpleEvent(['advertiser_id' => 0])]],
            [[static::simpleEvent(['advertiser_id' => 'invalid_value'])]],
            [[static::simpleEvent([], 'campaign_id')]],
            [[static::simpleEvent(['campaign_id' => null])]],
            [[static::simpleEvent(['campaign_id' => 0])]],
            [[static::simpleEvent(['campaign_id' => 'invalid_value'])]],
            [[static::simpleEvent([], 'banner_id')]],
            [[static::simpleEvent(['banner_id' => null])]],
            [[static::simpleEvent(['banner_id' => 0])]],
            [[static::simpleEvent(['banner_id' => 'invalid_value'])]],
        ];
    }

    protected static function invalidImpressionDataProvider(): array
    {
        return [
            [[static::simpleEvent([], 'impression_id')]],
            [[static::simpleEvent(['impression_id' => null])]],
            [[static::simpleEvent(['impression_id' => 0])]],
            [[static::simpleEvent(['impression_id' => 'invalid_value'])]],
            [[static::simpleEvent([], 'tracking_id')]],
            [[static::simpleEvent(['tracking_id' => null])]],
            [[static::simpleEvent(['tracking_id' => 0])]],
            [[static::simpleEvent(['tracking_id' => 'invalid_value'])]],
            [[static::simpleEvent([], 'user_id')]],
            [[static::simpleEvent(['user_id' => null])]],
            [[static::simpleEvent(['user_id' => 0])]],
            [[static::simpleEvent(['user_id' => 'invalid_value'])]],
            [[static::simpleEvent(['keywords' => 0])]],
            [[static::simpleEvent(['keywords' => 'invalid_value'])]],
            [[static::simpleEvent(['context' => 0])]],
            [[static::simpleEvent(['context' => 'invalid_value'])]],
            [[static::simpleEvent([], 'page_rank')]],
            [[static::simpleEvent(['page_rank' => null])]],
            [[static::simpleEvent(['page_rank' => -1])]],
            [[static::simpleEvent(['page_rank' => 100])]],
            [[static::simpleEvent(['page_rank' => 'invalid_value'])]],
            [[static::simpleEvent([], 'human_score')]],
            [[static::simpleEvent(['human_score' => null])]],
            [[static::simpleEvent(['human_score' => -1])]],
            [[static::simpleEvent(['human_score' => 100])]],
            [[static::simpleEvent(['human_score' => 'invalid_value'])]],
        ];
    }

    protected static function simpleEvent(array $mergeData = [], string $remove = null): array
    {
        $event = array_merge(
            [
                'id' => '43c567e1396b4cadb52223a51796fd01',
                'time' => time() - 5,
                'case_id' => '43c567e1396b4cadb52223a51796fd01',
                'publisher_id' => 'ffc567e1396b4cadb52223a51796fd02',
                'advertiser_id' => 'ccc567e1396b4cadb52223a51796fdcc',
                'campaign_id' => 'ddc567e1396b4cadb52223a51796fddd',
                'banner_id' => 'ddc567e1396b4cadb52223a51796fddd',
                'impression_id' => '13c567e1396b4cadb52223a51796fd03',
                'tracking_id' => '23c567e1396b4cadb52223a51796fd02',
                'user_id' => '33c567e1396b4cadb52223a51796fd01',
                'human_score' => 0.99,
                'page_rank' => 1.0,
            ],
            $mergeData
        );

        if ($remove !== null) {
            unset($event[$remove]);
        }

        return $event;
    }
}
