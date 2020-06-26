<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BidStrategyControllerTest extends WebTestCase
{
    public function testUpdateBidStrategy(): void
    {
        $parameters = [
            'bid_strategies' => [
                [
                    'id' => 'fff567e1396b4cadb52223a51796fd02',
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

    public function testEmptyUpdateBidStrategy(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/bid-strategies');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client = self::createClient();
        $client->request('POST', '/api/v1/bid-strategies', [], [], [], json_encode([]));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidUpdateBidStrategy(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/bid-strategies', [], [], [], 'invalid[]');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $parameters = [
            'bid-strategies' => [
                [
                    'id' => 'invalid',
                ],
            ],
        ];

        $client = self::createClient();
        $client->request('POST', '/api/v1/bid-strategies', [], [], [], json_encode($parameters));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testDeleteBidStrategy(): void
    {
        $parameters = [
            'bid-strategies' => [
                '43c567e1396b4cadb52223a51796fd01',
                'fff567e1396b4cadb52223a51796fd02',
            ],
        ];

        $client = self::createClient();
        $client->request('DELETE', '/api/v1/bid-strategies', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testEmptyDeleteBidStrategy(): void
    {
        $client = self::createClient();
        $client->request('DELETE', '/api/v1/bid-strategies');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client = self::createClient();
        $client->request('DELETE', '/api/v1/bid-strategies', [], [], [], json_encode([]));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidDeleteBidStrategy(): void
    {
        $client = self::createClient();
        $client->request('DELETE', '/api/v1/bid-strategies', [], [], [], 'invalid[]');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $parameters = [
            'bid-strategies' => [
                'invalid',
            ],
        ];

        $client = self::createClient();
        $client->request('DELETE', '/api/v1/bid-strategies', [], [], [], json_encode($parameters));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }
}
