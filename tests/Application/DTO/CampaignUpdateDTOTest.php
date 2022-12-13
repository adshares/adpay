<?php

declare(strict_types=1);

namespace App\Tests\Application\DTO;

use App\Application\DTO\CampaignUpdateDTO;
use App\Application\Exception\ValidationException;
use App\Domain\Model\Banner;
use App\Domain\Model\Campaign;
use App\Domain\Model\Conversion;
use DateTime;
use PHPUnit\Framework\TestCase;

final class CampaignUpdateDTOTest extends TestCase
{
    public function testEmptyInputData(): void
    {
        $this->expectException(ValidationException::class);

        new CampaignUpdateDTO([]);
    }

    public function testInvalidInputData(): void
    {
        $this->expectException(ValidationException::class);

        new CampaignUpdateDTO(['invalid' => []]);
    }

    /**
     * @dataProvider validCampaignsDataProvider
     */
    public function testValidCampaignsData(array $data, int $count = 1): void
    {
        $dto = new CampaignUpdateDTO(['campaigns' => $data]);

        $this->assertCount($count, $dto->getCampaigns());
    }

    /**
     * @dataProvider invalidCampaignsDataProvider
     */
    public function testInvalidCampaignsData(array $data): void
    {
        $this->expectException(ValidationException::class);

        new CampaignUpdateDTO(['campaigns' => $data]);
    }

    /**
     * @dataProvider validBannersDataProvider
     */
    public function testValidBannersData(array $data, int $count = 1): void
    {
        $dto = new CampaignUpdateDTO(
            [
                'campaigns' => [
                    self::simpleCampaign(['banners' => $data]),
                ],
            ]
        );

        $this->assertCount($count, $dto->getCampaigns()->first()->getBanners());
    }

    /**
     * @dataProvider invalidBannersDataProvider
     */
    public function testInvalidBannersData($data): void
    {
        $this->expectException(ValidationException::class);

        new CampaignUpdateDTO(
            [
                'campaigns' => [
                    self::simpleCampaign(['banners' => $data]),
                ],
            ]
        );
    }

    /**
     * @dataProvider validFiltersDataProvider
     */
    public function testValidFiltersData($data): void
    {
        $dto = new CampaignUpdateDTO(
            [
                'campaigns' => [
                    self::simpleCampaign(['filters' => $data]),
                ],
            ]
        );

        $this->assertCount(1, $dto->getCampaigns());
    }

    /**
     * @dataProvider invalidFiltersDataProvider
     */
    public function testInvalidFiltersData($data): void
    {
        $this->expectException(ValidationException::class);

        new CampaignUpdateDTO(
            [
                'campaigns' => [
                    self::simpleCampaign(['filters' => $data]),
                ],
            ]
        );
    }

    /**
     * @dataProvider validConversionsDataProvider
     */
    public function testValidConversionsData($data, int $count = 1): void
    {
        $dto = new CampaignUpdateDTO(
            [
                'campaigns' => [
                    self::simpleCampaign(['conversions' => $data]),
                ],
            ]
        );

        $this->assertCount($count, $dto->getCampaigns()->first()->getConversions());
    }

    /**
     * @dataProvider invalidConversionsDataProvider
     */
    public function testInvalidConversionsData($data): void
    {
        $this->expectException(ValidationException::class);

        new CampaignUpdateDTO(
            [
                'campaigns' => [
                    self::simpleCampaign(['conversions' => $data]),
                ],
            ]
        );
    }

    public function testModel(): void
    {
        $bannersInput = self::simpleBanner();
        $conversionInput = self::simpleConversion();

        $input = self::simpleCampaign(
            [
                'time_end' => (new DateTime())->getTimestamp() + 200,
                'max_cpm' => 100,
                'max_cpc' => 200,
                'banners' => [$bannersInput],
                'filters' => ['require' => ['a'], 'exclude' => ['b']],
                'conversions' => [$conversionInput],
                'medium' => 'metaverse',
            ]
        );
        $dto = new CampaignUpdateDTO(['campaigns' => [$input]]);

        /* @var $campaign Campaign */
        $campaign = $dto->getCampaigns()->first();
        /* @var $banner Banner */
        $banner = $campaign->getBanners()->first();
        /* @var $conversion Conversion */
        $conversion = $campaign->getConversions()->first();

        $this->assertEquals($input['id'], $campaign->getId());
        $this->assertEquals($input['advertiser_id'], $campaign->getAdvertiserId());
        $this->assertEquals($input['time_start'], $campaign->getTimeStart()->getTimestamp());
        $this->assertEquals($input['time_end'], $campaign->getTimeEnd()->getTimestamp());
        $this->assertEquals($input['budget'], $campaign->getBudgetValue());
        $this->assertEquals($input['max_cpm'], $campaign->getMaxCpm());
        $this->assertEquals($input['max_cpc'], $campaign->getMaxCpc());
        $this->assertEquals($input['filters'], $campaign->getFilters());
        $this->assertTrue($campaign->isMetaverse());

        $this->assertEquals($input['id'], $banner->getCampaignId());
        $this->assertEquals($bannersInput['id'], $banner->getId());
        $this->assertEquals($bannersInput['size'], $banner->getSize());
        $this->assertEquals($bannersInput['type'], $banner->getType());

        $this->assertEquals($input['id'], $conversion->getCampaignId());
        $this->assertEquals($conversionInput['id'], $conversion->getId());
        $this->assertEquals($conversionInput['limit_type'], $conversion->getLimitType());
        $this->assertEquals($conversionInput['is_repeatable'], $conversion->isRepeatable());
    }

    public function testDefaultMedium(): void
    {
        $input = self::simpleCampaign([], 'medium');
        $dto = new CampaignUpdateDTO(['campaigns' => [$input]]);

        /* @var $campaign Campaign */
        $campaign = $dto->getCampaigns()->first();
        $this->assertTrue($campaign->isWeb());
    }

    public function validCampaignsDataProvider(): array
    {
        return [
            [[], 0],
            [[self::simpleCampaign()]],
            [[self::simpleCampaign(), self::simpleCampaign()], 2],
            [[self::simpleCampaign(['time_end' => null])]],
            [[self::simpleCampaign(['time_end' => (new DateTime())->getTimestamp()])]],
            [[self::simpleCampaign(['time_end' => (new DateTime())->getTimestamp() + 100])]],
            [[self::simpleCampaign(['max_cpm' => null])]],
            [[self::simpleCampaign(['max_cpm' => 0])]],
            [[self::simpleCampaign(['max_cpm' => 200])]],
            [[self::simpleCampaign(['max_cpc' => null])]],
            [[self::simpleCampaign(['max_cpc' => 0])]],
            [[self::simpleCampaign(['max_cpc' => 100])]],
            [[self::simpleCampaign(['medium' => 'metaverse'])]],
            [[self::simpleCampaign([], 'medium')]],
            [[self::simpleCampaign(['vendor' => 'dummy'])]],
            [[self::simpleCampaign(['medium' => 'metaverse', 'vendor' => 'dummy'])]],
        ];
    }

    public function invalidCampaignsDataProvider(): array
    {
        return [
            [[self::simpleCampaign([], 'id')]],
            [[self::simpleCampaign(['id' => null])]],
            [[self::simpleCampaign(['id' => 0])]],
            [[self::simpleCampaign(['id' => 'invalid_value'])]],
            [[self::simpleCampaign([], 'advertiser_id')]],
            [[self::simpleCampaign(['advertiser_id' => null])]],
            [[self::simpleCampaign(['advertiser_id' => 0])]],
            [[self::simpleCampaign(['advertiser_id' => 'invalid_value'])]],
            [[self::simpleCampaign([], 'time_start')]],
            [[self::simpleCampaign(['time_start' => null])]],
            [[self::simpleCampaign(['time_start' => 'invalid_value'])]],
            [[self::simpleCampaign(['time_end' => 'invalid_value'])]],
            [[self::simpleCampaign([], 'budget')]],
            [[self::simpleCampaign(['budget' => null])]],
            [[self::simpleCampaign(['budget' => 0])]],
            [[self::simpleCampaign(['budget' => 'invalid_value'])]],
            [[self::simpleCampaign(['max_cpm' => 'invalid_value'])]],
            [[self::simpleCampaign(['max_cpc' => 'invalid_value'])]],
            [[self::simpleCampaign([], 'banners')]],
            [[self::simpleCampaign([], 'bid_strategy_id')]],
            [[self::simpleCampaign(['medium' => 'dummy'])]],
        ];
    }

    public function validBannersDataProvider(): array
    {
        return [
            [[], 0],
            [[self::simpleBanner()]],
            [[self::simpleBanner(), self::simpleBanner()], 2],
        ];
    }

    public function invalidBannersDataProvider(): array
    {
        return [
            [null],
            ['invalid_value'],
            [[self::simpleBanner([], 'id')]],
            [[self::simpleBanner(['id' => null])]],
            [[self::simpleBanner(['id' => 0])]],
            [[self::simpleBanner(['id' => 'invalid_value'])]],
            [[self::simpleBanner([], 'size')]],
            [[self::simpleBanner(['size' => null])]],
            [[self::simpleBanner(['size' => 0])]],
            [[self::simpleBanner(['size' => ''])]],
            [[self::simpleBanner([], 'type')]],
            [[self::simpleBanner(['type' => null])]],
            [[self::simpleBanner(['type' => 0])]],
            [[self::simpleBanner(['type' => 'invalid_value'])]],
        ];
    }

    public function validFiltersDataProvider(): array
    {
        return [
            [null],
            [[]],
            [['require' => null]],
            [['require' => []]],
            [['exclude' => null]],
            [['exclude' => []]],
            [['require' => null, 'exclude' => null]],
            [['require' => [], 'exclude' => []]],
        ];
    }

    public function invalidFiltersDataProvider(): array
    {
        return [
            ['invalid_value'],
            [['require' => 'invalid_value']],
            [['exclude' => 'invalid_value']],
        ];
    }

    public function validConversionsDataProvider(): array
    {
        return [
            [null, 0],
            [[], 0],
            [[self::simpleConversion()]],
            [[self::simpleConversion(), self::simpleConversion()], 2],
        ];
    }

    public function invalidConversionsDataProvider(): array
    {
        return [
            ['invalid_value'],
            [[self::simpleConversion([], 'id')]],
            [[self::simpleConversion(['id' => null])]],
            [[self::simpleConversion(['id' => 0])]],
            [[self::simpleConversion(['id' => 'invalid_value'])]],

            [[self::simpleConversion([], 'limit_type')]],
            [[self::simpleConversion(['limit_type' => null])]],
            [[self::simpleConversion(['limit_type' => 0])]],
            [[self::simpleConversion(['limit_type' => 'invalid_value'])]],

            [[self::simpleConversion([], 'is_repeatable')]],
            [[self::simpleConversion(['is_repeatable' => null])]],
            [[self::simpleConversion(['is_repeatable' => 4])]],
            [[self::simpleConversion(['is_repeatable' => 'invalid_value'])]],
        ];
    }

    private static function simpleBanner(array $mergeData = [], string $remove = null): array
    {
        $banner = array_merge(
            [
                'id' => '43c567e1396b4cadb52223a51796fd01',
                'size' => '220x345',
                'type' => 'image',
            ],
            $mergeData
        );

        if ($remove !== null) {
            unset($banner[$remove]);
        }

        return $banner;
    }

    private static function simpleConversion(array $mergeData = [], string $remove = null): array
    {
        $conversion = array_merge(
            [
                'id' => '249befbe667e49a7a5c93dfb9b21935c',
                'limit_type' => 'in_budget',
                'is_repeatable' => false
            ],
            $mergeData
        );

        if ($remove !== null) {
            unset($conversion[$remove]);
        }

        return $conversion;
    }

    private static function simpleCampaign(array $mergeData = [], string $remove = null): array
    {
        $campaign = array_merge(
            [
                'id' => '43c567e1396b4cadb52223a51796fd01',
                'advertiser_id' => 'fff567e1396b4cadb52223a51796fd02',
                'medium' => 'web',
                'time_start' => (new DateTime())->getTimestamp(),
                'budget' => 10000,
                'banners' => [self::simpleBanner()],
                'bid_strategy_id' => 'fff567e1396b4cadb52223a51796fd02',
            ],
            $mergeData
        );

        if ($remove !== null) {
            unset($campaign[$remove]);
        }

        return $campaign;
    }
}
