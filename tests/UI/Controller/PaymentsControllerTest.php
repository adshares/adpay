<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentsControllerTest extends WebTestCase
{
//    public function testGetPayments(): void
//    {
//        $client = static::createClient();
//
//        $client->request('GET', '/api/v1/payments/123123');
//        $this->assertEquals(200, $client->getResponse()->getStatusCode());
//    }

    public function testInvalidTimestamp(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/invalid');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testInvalidLimit(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/123123?limit=invalid');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testToHighLimit(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/123123?limit=1000000000');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidOffset(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/123123?offset=invalid');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }
}
