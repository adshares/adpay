<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventControllerTest extends WebTestCase
{
    public function testPostViews(): void
    {
        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => []
        ];

        $client = static::createClient();

        $client->request('POST', '/api/v1/views', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testPostClicks(): void
    {
        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => []
        ];

        $client = static::createClient();

        $client->request('POST', '/api/v1/clicks', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testPostConversions(): void
    {
        $parameters = [
            'time_start' => 123123123,
            'time_end' => 123123123,
            'events' => []
        ];

        $client = static::createClient();

        $client->request('POST', '/api/v1/conversions', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}
