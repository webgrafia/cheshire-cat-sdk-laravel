<?php

namespace CheshireCatSdk\Http\Clients;

use CheshireCatSdk\Exceptions\CheshireCatWebSocketException;
use WebSocket\Client;
use WebSocket\ConnectionException;

class WebSocketClient
{
    protected Client $client;
    protected string $wsBaseUri;

    public function __construct(string $wsBaseUri = 'ws://localhost:1865/ws')
    {
        $this->wsBaseUri = $wsBaseUri;
        $this->client = new Client($this->wsBaseUri);
    }

    public function sendMessage(array $payload)
    {
        try {
            $this->client->send(json_encode($payload));
        } catch (ConnectionException $e) {
            throw new CheshireCatWebSocketException("Error sending message: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function receive()
    {
        try {
            return $this->client->receive();
        } catch (ConnectionException $e) {
            throw new CheshireCatWebSocketException("Error receiving message: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function close()
    {
        try {
            $this->client->close();
        } catch (ConnectionException $e) {
            throw new CheshireCatWebSocketException("Error closing connection: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}