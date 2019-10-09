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
            'events' => [],
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

//        $parameters = [
//            'time_start' => 123123123,
//            'time_end' => 123123123,
//            'events' => [
//                [
//                    'id' => 'invalid',
//                ],
//            ],
//        ];
//
//        $client = self::createClient();
//        $client->request('POST', '/api/v1/events/views', [], [], [], json_encode($parameters));
//        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testUpdateClicks(): void
    {
        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => [],
        ];

        $client = static::createClient();

        $client->request('POST', '/api/v1/events/clicks', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testUpdateConversions(): void
    {
        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => [],
        ];

        $client = static::createClient();

        $client->request('POST', '/api/v1/events/conversions', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}
