<?php

declare(strict_types=1);

namespace App\Tests\UI\Controller;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

final class ReportControllerTest extends WebTestCase
{
    public function testGetEmptyReports(): void
    {
        $client = self::createClient();
        $this->getReports($client);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(['data' => []], $data);
    }

    public function testGetNoneExistingReports(): void
    {
        $client = self::createClient();
        $this->getReports($client, ['ids' => [7200]]);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(['data' => []], $data);
    }

    public function testGetReports(): void
    {
        $timestamp = $this->getHourTimestamp();
        $client = self::createClient();
        $this->addEvents($client, $timestamp - 200, $timestamp + 8000);

        $client->request('GET', '/api/v1/reports/' . $timestamp . '/payments?recalculate=1');
        $this->getReports($client);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals([
            'data' => [
                [
                    'id' => $timestamp - 3600,
                    'status' => 'incomplete',
                ],
                [
                    'id' => $timestamp,
                    'status' => 'calculated',
                ],
                [
                    'id' => $timestamp + 3600,
                    'status' => 'complete',
                ],
                [
                    'id' => $timestamp + 7200,
                    'status' => 'incomplete',
                ]
            ]
        ], $data);
    }

    public function testGetReportsByIds(): void
    {
        $timestamp = $this->getHourTimestamp();
        $client = self::createClient();
        $this->addEvents($client, $timestamp - 200, $timestamp + 8000);

        $this->getReports($client, ['ids' => [$timestamp, $timestamp + 7200]]);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals([
            'data' => [
                [
                    'id' => $timestamp,
                    'status' => 'complete',
                ],
                [
                    'id' => $timestamp + 7200,
                    'status' => 'incomplete',
                ]
            ]
        ], $data);
    }

    public function testInvalidId(): void
    {
        $client = self::createClient();
        $this->getReports($client, ['ids' => [1, 2, 'invalid', 4]]);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testInvalidIds(): void
    {
        $client = self::createClient();
        $this->getReports($client, ['ids' => 123]);
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
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

    private function getReports(AbstractBrowser $client, array $parameters = []): AbstractBrowser
    {
        $client->request('GET', '/api/v1/reports?' . http_build_query($parameters));
        return $client;
    }

    private function getHourTimestamp(): int
    {
        $timestamp = (new DateTimeImmutable('-1 day'))->getTimestamp();
        return (int)floor($timestamp / 3600) * 3600;
    }
}
