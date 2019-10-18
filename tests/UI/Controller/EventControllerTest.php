<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventControllerTest extends WebTestCase
{
    public function testUpdateViews(): void
    {
        $parameters = [
            'time_start' => time() - 10,
            'time_end' => time() - 1,
            'events' => [
                [
                    'id' => '43c567e1396b4cadb52223a51796fd01',
                    'time' => time() - 5,
                    'case_id' => '43c567e1396b4cadb52223a51796fd01',
                    'publisher_id' => 'ffc567e1396b4cadb52223a51796fd02',
                    'zone_id' => 'aac567e1396b4cadb52223a51796fdbb',
                    'advertiser_id' => 'ccc567e1396b4cadb52223a51796fdcc',
                    'campaign_id' => 'ddc567e1396b4cadb52223a51796fddd',
                    'banner_id' => 'ddc567e1396b4cadb52223a51796fddd',
                    'impression_id' => '13c567e1396b4cadb52223a51796fd03',
                    'tracking_id' => '23c567e1396b4cadb52223a51796fd02',
                    'user_id' => '33c567e1396b4cadb52223a51796fd01',
                    'page_rank' => 0.99,
                    'human_score' => 0.89,
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
            'time_start' => time() - 10,
            'time_end' => time() - 1,
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
            'time_start' => time() - 10,
            'time_end' => time() - 1,
            'events' => [
                [
                    'id' => '43c567e1396b4cadb52223a51796fd01',
                    'time' => time() - 5,
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
            'time_start' => time() - 10,
            'time_end' => time() - 1,
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
            'time_start' => time() - 10,
            'time_end' => time() - 1,
            'events' => [
                [
                    'id' => '43c567e1396b4cadb52223a51796fd01',
                    'time' => time() - 5,
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
                    'group_id' => 'fec567e1396b4cadb52223a51796fdff',
                    'conversion_id' => 'ffc567e1396b4cadb52223a51796fdff',
                    'conversion_value' => 1500,
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
            'time_start' => time() - 10,
            'time_end' => time() - 1,
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

    public function testDuplicateEvents(): void
    {
        $parameters1 = [
            'time_start' => time() - 10,
            'time_end' => time() - 1,
            'events' => [
                [
                    'id' => 'afc567e1396b4cadb52223a51796fdf1',
                    'time' => time() - 5,
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

        $client->request('POST', '/api/v1/events/views', [], [], [], json_encode($parameters1));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        $parameters1 = [
            'time_start' => time() - 110,
            'time_end' => time() - 101,
            'events' => [
                [
                    'id' => 'afc567e1396b4cadb52223a51796fdf1',
                    'time' => time() - 105,
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

        $client->request('POST', '/api/v1/events/views', [], [], [], json_encode($parameters1));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }
}
