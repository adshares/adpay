<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventControllerTest extends WebTestCase
{
    public function testPostEvents(): void
    {
        $parameters = [];

        $client = static::createClient();

        $client->request('POST', '/api/v1/events', [], [], [], json_encode($parameters));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}
