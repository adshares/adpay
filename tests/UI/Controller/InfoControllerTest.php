<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class InfoControllerTest extends WebTestCase
{
    public function testGetInfo(): void
    {
        $client = static::createClient();

        $client->request('GET', '/info');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/info.json');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/info.txt');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/info.html');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
