<?php

declare(strict_types=1);

namespace App\Tests\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

final class PaymentControllerTest extends WebTestCase
{
    public function testGetUnknownPayments(): void
    {
        $client = self::createClient();
        $this->getPayments($client, self::getTimestamp());
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testGetIncompletePayments(): void
    {
        $timestamp = self::getTimestamp();
        $client = self::createClient();
        $this->addEvents($client, $timestamp, $timestamp + 1800);
        $this->getPayments($client, $timestamp);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testGetNotReadyPayments(): void
    {
        $timestamp = self::getTimestamp();
        $client = self::createClient();
        $this->addEvents($client, $timestamp, $timestamp + 1800);
        $this->addEvents($client, $timestamp + 1801, $timestamp + 3600);
        $this->getPayments($client, $timestamp);
        $this->assertEquals(425, $client->getResponse()->getStatusCode());
    }

    public function testRecalculatePayments(): void
    {
        $timestamp = self::getTimestamp();
        $client = self::createClient();
        $this->addEvents($client, $timestamp, $timestamp + 3600);
        $this->getPayments($client, $timestamp, ['recalculate' => true]);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRecalculateIncompletePayments(): void
    {
        $timestamp = self::getTimestamp();
        $client = self::createClient();
        $this->addEvents($client, $timestamp, $timestamp + 500);
        $this->getPayments($client, $timestamp, ['recalculate' => true]);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testForceRecalculatePayments(): void
    {
        $timestamp = self::getTimestamp();
        $client = self::createClient();
        $this->addEvents($client, $timestamp, $timestamp + 500);
        $this->getPayments($client, $timestamp, ['recalculate' => true, 'force' => true]);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testInvalidTimestamp(): void
    {
        $client = self::createClient();
        $this->getPayments($client, -100);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testInvalidLimit(): void
    {
        $client = self::createClient();
        $this->getPayments($client, 123123, ['limit' => 'invalid']);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testToLowLimit(): void
    {
        $client = self::createClient();
        $this->getPayments($client, 123123, ['limit' => 0]);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testToHighLimit(): void
    {
        $client = self::createClient();
        $this->getPayments($client, 123123, ['limit' => 1000000000]);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidOffset(): void
    {
        $client = self::createClient();
        $this->getPayments($client, 123123, ['offset' => 'invalid']);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testToLowOffset(): void
    {
        $client = self::createClient();
        $this->getPayments($client, 123123, ['offset' => -100]);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    private static function getTimestamp(): int
    {
        return (int)floor(time() / 3600) * 3600 - 7200;
    }

    private function addEvents(AbstractBrowser $client, int $timeStart, int $timeEnd): AbstractBrowser
    {
        $parameters = [
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
            'events' => [],
        ];
        $client->request('POST', '/api/v1/events/views', [], [], [], json_encode($parameters));
        $client->request('POST', '/api/v1/events/clicks', [], [], [], json_encode($parameters));
        $client->request('POST', '/api/v1/events/conversions', [], [], [], json_encode($parameters));

        return $client;
    }

    private function getPayments(
        AbstractBrowser $client,
        int $timestamp,
        array $parameters = []
    ): AbstractBrowser {
        $client->request('GET', '/api/v1/reports/' . $timestamp . '/payments?' . http_build_query($parameters));
        return $client;
    }
}
