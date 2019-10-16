<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\Model\Payment;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Service\PaymentCalculator;
use Adshares\AdPay\Domain\ValueObject\BannerType;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Limit;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Domain\ValueObject\Size;
use Adshares\AdPay\Lib\DateTimeHelper;
use PHPUnit\Framework\TestCase;

final class PaymentCalculatorTest extends TestCase
{
    private const TIME = 1571231623;

    private const ADVERTISER_ID = '50000000000000000000000000000001';

    private const CAMPAIGN_ID = '60000000000000000000000000000001';

    private const BANNER_ID = '70000000000000000000000000000001';

    private const BANNER_SZIE = '100x200';

    private const USER_ID = 'a0000000000000000000000000000001';

    private const CONVERSION_ID = 'b0000000000000000000000000000001';

    public function testCampaignNotExist(): void
    {
        $campaigns = new CampaignCollection(self::campaign());

        $payment = $this->single($campaigns, self::viewEvent(['campaign_id' => '6000000000000000000000000000000f']));
        $this->assertEquals(PaymentStatus::CAMPAIGN_NOT_FOUND, $payment->getStatusCode());

        $payment = $this->single($campaigns, self::clickEvent(['campaign_id' => '6000000000000000000000000000000f']));
        $this->assertEquals(PaymentStatus::CAMPAIGN_NOT_FOUND, $payment->getStatusCode());

        $payment =
            $this->single($campaigns, self::conversionEvent(['campaign_id' => '6000000000000000000000000000000f']));
        $this->assertEquals(PaymentStatus::CAMPAIGN_NOT_FOUND, $payment->getStatusCode());
    }

    private function single(CampaignCollection $campaigns, array $event): Payment
    {
        $views = $event['type'] === EventType::VIEW ? [$event] : [];
        $clicks = $event['type'] === EventType::CLICK ? [$event] : [];
        $conversions = $event['type'] === EventType::CONVERSION ? [$event] : [];

        $report = new PaymentReport(1, PaymentReportStatus::createComplete());
        $payments = (new PaymentCalculator($report, $campaigns))->calculate($views, $clicks, $conversions);

        foreach ($payments as $payment) {
            return $payment;
        }

        return null;
    }

    private static function campaign(array $mergeData = [], array $banners = [], array $conversions = []): Campaign
    {
        $data = array_merge(
            [
                'id' => self::CAMPAIGN_ID,
                'advertiser_id' => self::ADVERTISER_ID,
                'time_start' => self::TIME - 7 * 24 * 3600,
                'time_end' => null,
                'filters' => [],
                'budget' => 1000,
                'max_cpm' => 100,
                'max_cpc' => null,
                'deleted_at' => null,
            ],
            $mergeData
        );

        $budget = new Budget(
            $data['budget'],
            $data['max_cpm'] !== null ? $data['max_cpm'] : null,
            $data['max_cpc'] !== null ? $data['max_cpc'] : null
        );

        return new Campaign(
            new Id($data['id']),
            new Id($data['advertiser_id']),
            DateTimeHelper::fromTimestamp($data['time_start']),
            $data['time_end'] !== null ? DateTimeHelper::fromTimestamp($data['time_end']) : null,
            $budget,
            new BannerCollection(...$banners),
            $data['filters'],
            new ConversionCollection(...$conversions),
            $data['deleted_at'] !== null ? DateTimeHelper::fromTimestamp($data['deleted_at']) : null
        );
    }

    private static function banner(array $mergeData = []): Banner
    {
        $data = array_merge(
            [
                'id' => self::BANNER_ID,
                'campaign_id' => self::CAMPAIGN_ID,
                'size' => self::BANNER_SZIE,
                'type' => BannerType::IMAGE,
                'deleted_at' => null,
            ],
            $mergeData
        );

        return new Banner(
            new Id($data['id']),
            new Id($data['campaign_id']),
            Size::fromString($data['size']),
            new BannerType($data['type']),
            $data['deleted_at'] !== null ? DateTimeHelper::fromTimestamp($data['deleted_at']) : null
        );
    }

    private static function conversion(array $mergeData = []): Conversion
    {
        $data = array_merge(
            [
                'id' => self::CONVERSION_ID,
                'campaign_id' => self::CAMPAIGN_ID,
                'limit' => null,
                'limit_type' => LimitType::IN_BUDGET,
                'cost' => 0,
                'value' => 10,
                'is_value_mutable' => false,
                'is_repeatable' => false,
                'deleted_at' => null,
            ],
            $mergeData
        );

        $limit =
            new Limit(
                $data['limit'] !== null ? $data['limit'] : null,
                new LimitType($data['limit_type']),
                $data['cost']
            );

        return new Conversion(
            new Id($data['id']),
            new Id($data['campaign_id']),
            $limit,
            $data['value'],
            $data['is_value_mutable'],
            $data['is_repeatable'],
            $data['deleted_at'] !== null ? DateTimeHelper::fromTimestamp($data['deleted_at']) : null
        );
    }

    private static function viewEvent(array $mergeData = []): array
    {
        return array_merge(
            self::event(),
            [
                'type' => EventType::VIEW,
            ],
            $mergeData
        );
    }

    private static function clickEvent(array $mergeData = []): array
    {
        return array_merge(
            self::event(),
            [
                'type' => EventType::CLICK,
            ],
            $mergeData
        );
    }

    private static function conversionEvent(array $mergeData = []): array
    {
        return array_merge(
            self::event(),
            [
                'type' => EventType::CONVERSION,
                'conversion_id' => self::CONVERSION_ID,
                'conversion_value' => 100,
            ],
            $mergeData
        );
    }

    private static function event(): array
    {
        return [
            'id' => '10000000000000000000000000000001',
            'time' => self::TIME,
            'payment_status' => null,
            'case_id' => '20000000000000000000000000000001',
            'publisher_id' => '30000000000000000000000000000001',
            'zone_id' => '40000000000000000000000000000001',
            'advertiser_id' => self::ADVERTISER_ID,
            'campaign_id' => self::CAMPAIGN_ID,
            'banner_id' => self::BANNER_ID,
            'impression_id' => '80000000000000000000000000000001',
            'tracking_id' => '90000000000000000000000000000001',
            'user_id' => self::USER_ID,
            'human_score' => 0.9,
            'keywords' => [],
            'context' => [],
        ];
    }
}
