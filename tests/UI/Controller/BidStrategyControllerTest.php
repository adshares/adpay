<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BidStrategyControllerTest extends WebTestCase
{
    public function testUpdateCampaign(): void
    {
        $parameters = [
            'bid_strategies' => [
                [
                    'uuid' => 'fff567e1396b4cadb52223a51796fd02',
                    'name' => 'Test bid strategy',
                    'details' => [
                        [
                            'category' => 'user:country:st',
                            'rank' => 0.98,
                        ],
                    ],
                ],
            ],
        ];

        $client = self::createClient();
        $client->request('POST', '/api/v1/bid-strategies', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testEmptyUpdateCampaign(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/bid-strategies');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client = self::createClient();
        $client->request('POST', '/api/v1/bid-strategies', [], [], [], json_encode([]));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidUpdateCampaign(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/bid-strategies', [], [], [], 'invalid[]');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $parameters = [
            'bid-strategies' => [
                [
                    'uuid' => 'invalid',
                ],
            ],
        ];

        $client = self::createClient();
        $client->request('POST', '/api/v1/bid-strategies', [], [], [], json_encode($parameters));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }
}
