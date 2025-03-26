<?php

namespace Tests\Unit;

use CheshireCatSdk\Exceptions\CheshireCatWebSocketException;
use CheshireCatSdk\Http\Clients\WebSocketClient;
use PHPUnit\Framework\TestCase;
use WebSocket\Client;
use WebSocket\ConnectionException;

class WebSocketClientTest extends TestCase
{
    protected WebSocketClient $client;
    protected $mockWebSocketClient;
    protected string $wsBaseUri = 'ws://test-base-uri';
    protected function setUp(): void
    {
        parent::setUp();
        // Mock the WebSocket\Client class
        $this->mockWebSocketClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create an instance of WebSocketClient and inject the mock
        $this->client = new WebSocketClient($this->wsBaseUri);
        $reflection = new \ReflectionClass($this->client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->client, $this->mockWebSocketClient);
    }

    public function testSendMessageFailure(): void
    {
        $payload = ['text' => 'Hello'];
        $this->mockWebSocketClient->expects($this->once())
            ->method('send')
            ->with(json_encode($payload))
            ->willThrowException(new ConnectionException('Connection error'));

        $this->expectException(CheshireCatWebSocketException::class);
        $this->client->sendMessage($payload);
    }

    public function testReceiveFailure(): void
    {
        $this->mockWebSocketClient->expects($this->once())
            ->method('receive')
            ->willThrowException(new ConnectionException('Connection error'));

        $this->expectException(CheshireCatWebSocketException::class);
        $this->client->receive();
    }

    public function testCloseFailure(): void
    {
        $this->mockWebSocketClient->expects($this->once())
            ->method('close')
            ->willThrowException(new ConnectionException('Connection error'));

        $this->expectException(CheshireCatWebSocketException::class);
        $this->client->close();
    }
}