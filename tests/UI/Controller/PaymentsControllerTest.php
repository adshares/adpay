<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentsControllerTest extends WebTestCase
{
    public function testGetPayments(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/123123');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testInvalidTimestamp(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/invalid');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
