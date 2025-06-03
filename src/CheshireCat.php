<?php

namespace CheshireCatSdk;

use CheshireCatSdk\Http\Clients\CheshireCatClient;
use CheshireCatSdk\Http\Clients\WebSocketClient;
use Illuminate\Support\Facades\Config;

class CheshireCat
{
    protected $client;
    public $wsClient; //aggiunta variabile pubblica

    /**
     * CheshireCat constructor.
     * Initializes REST and WebSocket clients.
     */
    public function __construct()
    {
        $this->client = new CheshireCatClient();
        $this->wsClient = new WebSocketClient();
    }

    /**
     * Handles access to REST client methods dynamically.
     *
     * @param string $method Method name to call.
     * @param array $arguments Arguments to be passed to the method.
     * @return mixed Method return value.
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->client, $method], $arguments);
    }

    /**
     * Retrieves the status of the CheshireCat service.
     *
     * @return mixed HTTP response containing the status.
     */
    public function status()
    {
        return $this->client->getStatus();
    }

    /**
     * Sends a message using the REST API.
     *
     * @param string $text The message text to send.
     * @param string|null $user_id Optional user ID to associate with the message.
     * @return mixed HTTP response from the API.
     */
    public function message($text, $user_id = null)
    {
        $payload = ['text' => $text];
        $headers = [];

        if ($user_id !== null) {
            $headers['user_id'] = $user_id;
        }

        return $this->client->sendMessage($payload, $headers);
    }

    /**
     * Sends a message using the WebSocket client.
     *
     * @param array $payload The payload to send as a message.
     * @return mixed WebSocket response.
     */
    public function sendMessageViaWebSocket(array $payload)
    {
        return $this->wsClient->sendMessage($payload);
    }

    /**
     * Closes the WebSocket connection.
     *
     * @return void
     */
    public function closeWebSocketConnection()
    {
        $this->wsClient->close();
    }
}
