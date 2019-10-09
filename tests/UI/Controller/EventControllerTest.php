<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventControllerTest extends WebTestCase
{
    public function testUpdateViews(): void
    {
        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => [
                [
                    'id' => '43c567e1396b4cadb52223a51796fd01',
                    'time' => 123123123,
                    'case_id' => '43c567e1396b4cadb52223a51796fd01',
                    'publisher_id' => 'ffc567e1396b4cadb52223a51796fd02',
                    'zone_id' => 'aac567e1396b4cadb52223a51796fdbb',
                    'advertiser_id' => 'ccc567e1396b4cadb52223a51796fdcc',
                    'campaign_id' => 'ddc567e1396b4cadb52223a51796fddd',
                    'banner_id' => 'ddc567e1396b4cadb52223a51796fddd',
                    'impression_id' => '13c567e1396b4cadb52223a51796fd03',
                    'tracking_id' => '23c567e1396b4cadb52223a51796fd02',
                    'user_id' => '33c567e1396b4cadb52223a51796fd01',
                    'human_score' => 0.99,
                ],
            ],
        ];

        $client = static::createClient();

        $client->request('POST', '/api/v1/events/views', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testEmptyUpdateViews(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/events/views');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client = self::createClient();
        $client->request('POST', '/api/v1/events/views', [], [], [], json_encode([]));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidUpdateViews(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/events/views', [], [], [], 'invalid[]');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => [
                [
                    'id' => 'invalid',
                ],
            ],
        ];

        $client = self::createClient();
        $client->request('POST', '/api/v1/events/views', [], [], [], json_encode($parameters));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testUpdateClicks(): void
    {
        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => [
                [
                    'id' => '43c567e1396b4cadb52223a51796fd01',
                    'time' => 123123123,
                    'case_id' => '43c567e1396b4cadb52223a51796fd01',
                    'publisher_id' => 'ffc567e1396b4cadb52223a51796fd02',
                    'zone_id' => 'aac567e1396b4cadb52223a51796fdbb',
                    'advertiser_id' => 'ccc567e1396b4cadb52223a51796fdcc',
                    'campaign_id' => 'ddc567e1396b4cadb52223a51796fddd',
                    'banner_id' => 'ddc567e1396b4cadb52223a51796fddd',
                    'impression_id' => '13c567e1396b4cadb52223a51796fd03',
                    'tracking_id' => '23c567e1396b4cadb52223a51796fd02',
                    'user_id' => '33c567e1396b4cadb52223a51796fd01',
                    'human_score' => 0.99,
                ],
            ],
        ];

        $client = static::createClient();

        $client->request('POST', '/api/v1/events/clicks', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testEmptyUpdateClicks(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/events/clicks');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client = self::createClient();
        $client->request('POST', '/api/v1/events/clicks', [], [], [], json_encode([]));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidUpdateClicks(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/events/clicks', [], [], [], 'invalid[]');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => [
                [
                    'id' => 'invalid',
                ],
            ],
        ];

        $client = self::createClient();
        $client->request('POST', '/api/v1/events/clicks', [], [], [], json_encode($parameters));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testUpdateConversions(): void
    {
        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => [
                [
                    'id' => '43c567e1396b4cadb52223a51796fd01',
                    'time' => 123123123,
                    'case_id' => '43c567e1396b4cadb52223a51796fd01',
                    'publisher_id' => 'ffc567e1396b4cadb52223a51796fd02',
                    'zone_id' => 'aac567e1396b4cadb52223a51796fdbb',
                    'advertiser_id' => 'ccc567e1396b4cadb52223a51796fdcc',
                    'campaign_id' => 'ddc567e1396b4cadb52223a51796fddd',
                    'banner_id' => 'ddc567e1396b4cadb52223a51796fddd',
                    'impression_id' => '13c567e1396b4cadb52223a51796fd03',
                    'tracking_id' => '23c567e1396b4cadb52223a51796fd02',
                    'user_id' => '33c567e1396b4cadb52223a51796fd01',
                    'human_score' => 0.99,
                    'conversion_id' => 'ffc567e1396b4cadb52223a51796fdff',
                ],
            ],
        ];

        $client = static::createClient();

        $client->request('POST', '/api/v1/events/conversions', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testEmptyUpdateConversions(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/events/conversions');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client = self::createClient();
        $client->request('POST', '/api/v1/events/conversions', [], [], [], json_encode([]));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidUpdateConversions(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/events/conversions', [], [], [], 'invalid[]');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => [
                [
                    'id' => 'invalid',
                ],
            ],
        ];

        $client = self::createClient();
        $client->request('POST', '/api/v1/events/conversions', [], [], [], json_encode($parameters));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }
}
