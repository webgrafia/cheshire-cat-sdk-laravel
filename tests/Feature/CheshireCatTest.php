<?php
namespace Tests\Feature;
use Tests\TestCase;
use CheshireCatSdk\Facades\CheshireCatFacade as CheshireCat;
use CheshireCatSdk\CheshireCatServiceProvider;
class CheshireCatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Register the service provider manually for testing
        $this->app->register(CheshireCatServiceProvider::class);
    }
    /**
     * Test the status API to ensure it returns a 200 status code.
     *
     * @return void
     */
    public function testStatus()
    {
        $response = CheshireCat::status();
        $this->assertEquals(200, $response->getStatusCode());
    }
    /**
     * Test sending a message via the WebSocket and verify the response structure.
     *
     * @return void
     */
    public function testWebSocketConnection()
    {
        $response = CheshireCat::sendMessageViaWebSocket(['text' => 'Hello WebSocket!']);
        $this->assertArrayHasKey('response', $response);
    }
    /**
     * Test closing the WebSocket connection.
     *
     * @return void
     */
    public function testCloseWebSocketConnection()
    {
        CheshireCat::closeWebSocketConnection();
        $this->assertTrue(true);
    }
}
