<?php

namespace Tests\Unit;

use CheshireCatSdk\Exceptions\CheshireCatApiException;
use CheshireCatSdk\Exceptions\CheshireCatAuthenticationException;
use CheshireCatSdk\Exceptions\CheshireCatNotFoundException;
use CheshireCatSdk\Exceptions\CheshireCatValidationException;
use CheshireCatSdk\Http\Clients\CheshireCatClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @covers \CheshireCatSdk\Http\Clients\CheshireCatClient
 * @uses \CheshireCatSdk\Exceptions\CheshireCatApiException
 * @uses \CheshireCatSdk\Exceptions\CheshireCatAuthenticationException
 * @uses \CheshireCatSdk\Exceptions\CheshireCatNotFoundException
 * @uses \CheshireCatSdk\Exceptions\CheshireCatValidationException
 */
class ConnectionTest extends TestCase
{
    protected CheshireCatClient $client;
    protected MockHandler $mockHandler;
    protected string $baseUri = 'http://localhost:1865';
    protected string $apiKey = 'test-api-key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $this->client = new CheshireCatClient($this->baseUri, $this->apiKey);
        $reflection = new \ReflectionClass($this->client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->client, $guzzleClient);
    }

    public function testGetStatusSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"status": "ok"}'));
        $response = $this->client->getStatus();
        $this->assertRequest('GET', '/');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetStatusFailure(): void
    {
        $this->mockHandler->append(new Response(500, [], '{"error": "Internal Server Error"}'));
        $this->expectException(CheshireCatApiException::class);
        $this->client->getStatus();
        $this->assertRequest('GET', '/');
    }

    public function testGetStatusConnectionFailure(): void
    {
        $this->mockHandler->append(new ConnectException('Connection error', new Request('GET', '/')));
        $this->expectException(CheshireCatApiException::class);
        $this->client->getStatus();
        $this->assertRequest('GET', '/');
    }

    protected function assertRequest(string $method, string $uri, array $options = []): void
    {
        $request = $this->mockHandler->getLastRequest();
        $this->assertEquals($method, $request->getMethod());

    }
}