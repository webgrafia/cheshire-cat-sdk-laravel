<?php

namespace CheshireCatSdk\Http\Clients;

use WebSocket\Client;

class WebSocketClient
{
    protected $client;

    /**
     * WebSocketClient constructor.
     * Initializes the WebSocket client with the configured base URI.
     */
    public function __construct()
    {
        $url = config('cheshirecat.ws_base_uri');
        $this->client = new Client($url, ['timeout' => 60]);
    }

    /**
     * Sends a message to the WebSocket server.
     *
     * @param array $payload The payload to send to the WebSocket server.
     * @return void
     */
    public function sendMessage(array $payload)
    {
        $this->client->send(json_encode($payload));
    }

    /**
     * Receives a message from the WebSocket server.
     *
     * @return string The message received from the WebSocket server.
     */
    public function receive()
    {
        return $this->client->receive();
    }

    /**
     * Closes the WebSocket connection.
     */
    public function close()
    {
        $this->client->close();
    }
}