<?php

declare(strict_types=1);

namespace App\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentsControllerTest extends WebTestCase
{
    public function testRecalculatePayments(): void
    {
        $timestamp = (int)floor(time() / 3600) * 3600 - 7200;

        $parameters = [
            'time_start' => $timestamp,
            'time_end' => $timestamp + 3600,
            'events' => [],
        ];

        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/' . $timestamp);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client->request('POST', '/api/v1/events/views', [], [], [], json_encode($parameters));
        $client->request('POST', '/api/v1/events/clicks', [], [], [], json_encode($parameters));
        $client->request('POST', '/api/v1/events/conversions', [], [], [], json_encode($parameters));

        $client->request('GET', '/api/v1/payments/' . $timestamp . '?recalculate=1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRecalculateIncompletePayments(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/123123?recalculate=1');
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testForceRecalculatePayments(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/123123?recalculate=1&force=1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testGetIncompletePayments(): void
    {
        $timestamp = (int)floor(time() / 3600) * 3600 - 7200;

        $parameters = [
            'time_start' => $timestamp,
            'time_end' => $timestamp + 3600,
            'events' => [],
        ];

        $client = static::createClient();

        $client->request('GET', '/api/v1/payments/' . $timestamp);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $client->request('POST', '/api/v1/events/views', [], [], [], json_encode($parameters));
        $client->request('POST', '/api/v1/events/clicks', [], [], [], json_encode($parameters));
        $client->request('POST', '/api/v1/events/conversions', [], [], [], json_encode($parameters));

        $client->request('GET', '/api/v1/payments/' . $timestamp);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

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
