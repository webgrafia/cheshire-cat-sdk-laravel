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
        $url = config('cheshirecat.ws_base_uri'); // Aggiungere questa variabile alla configurazione
        $this->client = new Client($url);
    }
    /**
     * Sends a message to the WebSocket server and retrieves the response.
     *
     * @param array $payload The payload to send to the WebSocket server.
     * @return array The response received from the WebSocket server, decoded as an associative array.
     */
    public function sendMessage(array $payload)
    {
        $this->client->send(json_encode($payload));
        return json_decode($this->client->receive(), true);
    }
    /**
     * Closes the WebSocket connection.
     */
    public function close()
    {
        $this->client->close();
    }
}
