<?php

declare(strict_types=1);

namespace App\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CampaignControllerTest extends WebTestCase
{
    public function testUpdateCampaign(): void
    {
        $parameters = [
            'campaigns' => [
                [
                    'id' => '43c567e1396b4cadb52223a51796fd01',
                    'advertiser_id' => 'fff567e1396b4cadb52223a51796fd02',
                    'time_start' => 123123,
                    'budget' => 10000,
                    'banners' => [
                        [
                            'id' => '43c567e1396b4cadb52223a51796fd02',
                            'size' => '220x345',
                            'type' => 'image',
                        ],
                    ],
                    'conversions' => [
                        [
                            'id' => '249befbe667e49a7a5c93dfb9b219352',
                            'limit_type' => 'in_budget',
                            'is_repeatable' => false,
                        ],
                    ],
                    'bid_strategy_id' => 'fff567e1396b4cadb52223a51796fd02',
                ],
            ],
        ];

        $client = self::createClient();
        $client->request('POST', '/api/v1/campaigns', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testUpdateMetaverseCampaign(): void
    {
        $parameters = [
            'campaigns' => [
                [
                    'id' => '43c567e1396b4cadb52223a51796fd01',
                    'advertiser_id' => 'fff567e1396b4cadb52223a51796fd02',
                    'medium' => 'metaverse',
                    'vendor' => 'my-metaverse',
                    'time_start' => 123123,
                    'budget' => 10000,
                    'banners' => [
                        [
                            'id' => '43c567e1396b4cadb52223a51796fd02',
                            'size' => '220x345',
                            'type' => 'image',
                        ],
                    ],
                    'bid_strategy_id' => 'fff567e1396b4cadb52223a51796fd02',
                ],
            ],
        ];

        $client = self::createClient();
        $client->request('POST', '/api/v1/campaigns', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testEmptyUpdateCampaign(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/campaigns');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client->request('POST', '/api/v1/campaigns', [], [], [], json_encode([]));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidUpdateCampaign(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/campaigns', [], [], [], 'invalid[]');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $parameters = [
            'campaigns' => [
                [
                    'id' => 'invalid',
                ],
            ],
        ];

        $client->request('POST', '/api/v1/campaigns', [], [], [], json_encode($parameters));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testDeleteCampaign(): void
    {
        $parameters = [
            'campaigns' => [
                '43c567e1396b4cadb52223a51796fd01',
                'fff567e1396b4cadb52223a51796fd02',
            ],
        ];

        $client = self::createClient();
        $client->request('DELETE', '/api/v1/campaigns', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testEmptyDeleteCampaign(): void
    {
        $client = self::createClient();
        $client->request('DELETE', '/api/v1/campaigns');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/api/v1/campaigns', [], [], [], json_encode([]));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidDeleteCampaign(): void
    {
        $client = self::createClient();
        $client->request('DELETE', '/api/v1/campaigns', [], [], [], 'invalid[]');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $parameters = [
            'campaigns' => [
                'invalid',
            ],
        ];

        $client->request('DELETE', '/api/v1/campaigns', [], [], [], json_encode($parameters));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }
}
