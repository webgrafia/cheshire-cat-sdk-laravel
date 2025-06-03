<?php

namespace Tests\Unit;

use CheshireCatSdk\Exceptions\CheshireCatApiException;
use CheshireCatSdk\Exceptions\CheshireCatAuthenticationException;
use CheshireCatSdk\Exceptions\CheshireCatFileUploadException;
use CheshireCatSdk\Exceptions\CheshireCatNotFoundException;
use CheshireCatSdk\Exceptions\CheshireCatValidationException;
use CheshireCatSdk\Http\Clients\CheshireCatClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CheshireCatClientTest extends TestCase
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
        $this->assertEquals('{"status": "ok"}', $response->getBody()->getContents());
    }

    public function testGetStatusFailure(): void
    {
        $this->mockHandler->append(new Response(500, [], '{"error": "Internal Server Error"}'));
        $this->expectException(CheshireCatApiException::class);
        $this->client->getStatus();
        $this->assertRequest('GET', '/');
    }

    public function testSendMessageSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"text": "Hello back!"}'));
        $response = $this->client->sendMessage(['text' => 'Hello!']);
        $this->assertRequest('POST', '/message', ['json' => ['text' => 'Hello!']]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"text": "Hello back!"}', $response->getBody()->getContents());
    }

    public function testSendMessageWithHeadersSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"text": "Hello back!"}'));
        $headers = ['user_id' => 'user123'];
        $response = $this->client->sendMessage(['text' => 'Hello!'], $headers);
        $this->assertRequest('POST', '/message', ['json' => ['text' => 'Hello!'], 'headers' => $headers]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"text": "Hello back!"}', $response->getBody()->getContents());
    }

    public function testSendMessageFailure(): void
    {
        $this->mockHandler->append(new Response(400, [], '{"error": "Bad Request"}'));
        $this->expectException(CheshireCatApiException::class);
        $this->client->sendMessage(['text' => 'Hello!']);
        $this->assertRequest('POST', '/message', ['json' => ['text' => 'Hello!']]);
    }

    public function testGetTokenSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"access_token": "token123"}'));
        $response = $this->client->getToken(['username' => 'test', 'password' => 'pass']);
        $this->assertRequest('POST', '/auth/token', ['json' => ['username' => 'test', 'password' => 'pass']]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"access_token": "token123"}', $response->getBody()->getContents());
    }

    public function testGetTokenFailure(): void
    {
        $this->mockHandler->append(new Response(401, [], '{"error": "Unauthorized"}'));
        $this->expectException(CheshireCatAuthenticationException::class);
        $this->client->getToken(['username' => 'test', 'password' => 'wrong']);
        $this->assertRequest('POST', '/auth/token', ['json' => ['username' => 'test', 'password' => 'wrong']]);
    }

    public function testGetAvailablePermissionsSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"permissions": ["read", "write"]}'));
        $response = $this->client->getAvailablePermissions();
        $this->assertRequest('GET', '/auth/available-permissions');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"permissions": ["read", "write"]}', $response->getBody()->getContents());
    }

    public function testGetAvailablePermissionsFailure(): void
    {
        $this->mockHandler->append(new Response(500, [], '{"error": "Internal Server Error"}'));
        $this->expectException(CheshireCatApiException::class);
        $this->client->getAvailablePermissions();
        $this->assertRequest('GET', '/auth/available-permissions');
    }

    public function testCreateUserSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"id": "user123"}'));
        $response = $this->client->createUser(['username' => 'test', 'password' => 'pass']);
        $this->assertRequest('POST', '/users/', ['json' => ['username' => 'test', 'password' => 'pass']]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"id": "user123"}', $response->getBody()->getContents());
    }

    public function testCreateUserFailure(): void
    {
        $this->mockHandler->append(new Response(422, [], '{"error": "Validation Error"}'));
        $this->expectException(CheshireCatValidationException::class);
        $this->client->createUser(['username' => 'test', 'password' => 'pass']);
        $this->assertRequest('POST', '/users/', ['json' => ['username' => 'test', 'password' => 'pass']]);
    }

    public function testGetUsersSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '[{"id": "user1"}, {"id": "user2"}]'));
        $response = $this->client->getUsers(0, 2);
        $this->assertRequest('GET', '/users/', ['query' => ['skip' => 0, 'limit' => 2]]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('[{"id": "user1"}, {"id": "user2"}]', $response->getBody()->getContents());
    }

    public function testGetUsersFailure(): void
    {
        $this->mockHandler->append(new Response(500, [], '{"error": "Internal Server Error"}'));
        $this->expectException(CheshireCatApiException::class);
        $this->client->getUsers(0, 2);
        $this->assertRequest('GET', '/users/', ['query' => ['skip' => 0, 'limit' => 2]]);
    }

    public function testGetUserSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"id": "user123"}'));
        $response = $this->client->getUser('user123');
        $this->assertRequest('GET', '/users/user123');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"id": "user123"}', $response->getBody()->getContents());
    }

    public function testGetUserFailure(): void
    {
        $this->mockHandler->append(new Response(404, [], '{"error": "Not Found"}'));
        $this->expectException(CheshireCatNotFoundException::class);
        $this->client->getUser('user123');
        $this->assertRequest('GET', '/users/user123');
    }

    public function testUpdateUserSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"id": "user123", "username": "updated"}'));
        $response = $this->client->updateUser('user123', ['username' => 'updated']);
        $this->assertRequest('PUT', '/users/user123', ['json' => ['username' => 'updated']]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"id": "user123", "username": "updated"}', $response->getBody()->getContents());
    }

    public function testUpdateUserFailure(): void
    {
        $this->mockHandler->append(new Response(404, [], '{"error": "Not Found"}'));
        $this->expectException(CheshireCatNotFoundException::class);
        $this->client->updateUser('user123', ['username' => 'updated']);
        $this->assertRequest('PUT', '/users/user123', ['json' => ['username' => 'updated']]);
    }

    public function testDeleteUserSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"message": "User deleted"}'));
        $response = $this->client->deleteUser('user123');
        $this->assertRequest('DELETE', '/users/user123');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message": "User deleted"}', $response->getBody()->getContents());
    }

    public function testDeleteUserFailure(): void
    {
        $this->mockHandler->append(new Response(404, [], '{"error": "Not Found"}'));
        $this->expectException(CheshireCatNotFoundException::class);
        $this->client->deleteUser('user123');
        $this->assertRequest('DELETE', '/users/user123');
    }

    public function testGetSettingsSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"setting1": "value1"}'));
        $response = $this->client->getSettings();
        $this->assertRequest('GET', '/settings/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"setting1": "value1"}', $response->getBody()->getContents());
    }

    public function testGetSettingsFailure(): void
    {
        $this->mockHandler->append(new Response(500, [], '{"error": "Internal Server Error"}'));
        $this->expectException(CheshireCatApiException::class);
        $this->client->getSettings();
        $this->assertRequest('GET', '/settings/');
    }

    public function testCreateSettingSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"message": "Setting created"}'));
        $response = $this->client->createSetting(['name' => 'new_setting', 'value' => 'value']);
        $this->assertRequest('POST', '/settings/', ['json' => ['name' => 'new_setting', 'value' => 'value']]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message": "Setting created"}', $response->getBody()->getContents());
    }

    public function testCreateSettingFailure(): void
    {
        $this->mockHandler->append(new Response(422, [], '{"error": "Validation Error"}'));
        $this->expectException(CheshireCatValidationException::class);
        $this->client->createSetting(['name' => 'new_setting', 'value' => 'value']);
        $this->assertRequest('POST', '/settings/', ['json' => ['name' => 'new_setting', 'value' => 'value']]);
    }

    public function testGetSettingSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"name": "setting1", "value": "value1"}'));
        $response = $this->client->getSetting('setting1');
        $this->assertRequest('GET', '/settings/setting1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"name": "setting1", "value": "value1"}', $response->getBody()->getContents());
    }

    public function testGetSettingFailure(): void
    {
        $this->mockHandler->append(new Response(404, [], '{"error": "Not Found"}'));
        $this->expectException(CheshireCatNotFoundException::class);
        $this->client->getSetting('setting1');
        $this->assertRequest('GET', '/settings/setting1');
    }

    public function testUpdateSettingSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"name": "setting1", "value": "updated"}'));
        $response = $this->client->updateSetting('setting1', ['value' => 'updated']);
        $this->assertRequest('PUT', '/settings/setting1', ['json' => ['value' => 'updated']]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"name": "setting1", "value": "updated"}', $response->getBody()->getContents());
    }

    public function testUpdateSettingFailure(): void
    {
        $this->mockHandler->append(new Response(404, [], '{"error": "Not Found"}'));
        $this->expectException(CheshireCatNotFoundException::class);
        $this->client->updateSetting('setting1', ['value' => 'updated']);
        $this->assertRequest('PUT', '/settings/setting1', ['json' => ['value' => 'updated']]);
    }

    public function testDeleteSettingSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"message": "Setting deleted"}'));
        $response = $this->client->deleteSetting('setting1');
        $this->assertRequest('DELETE', '/settings/setting1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message": "Setting deleted"}', $response->getBody()->getContents());
    }

    public function testDeleteSettingFailure(): void
    {
        $this->mockHandler->append(new Response(404, [], '{"error": "Not Found"}'));
        $this->expectException(CheshireCatNotFoundException::class);
        $this->client->deleteSetting('setting1');
        $this->assertRequest('DELETE', '/settings/setting1');
    }

    public function testGetMemoryPointsSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '[{"id": "point1"}, {"id": "point2"}]'));
        $response = $this->client->getMemoryPoints('collection1', 2, 0);
        $this->assertRequest('GET', '/memory/collections/collection1/points', ['query' => ['limit' => 2, 'offset' => 0]]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('[{"id": "point1"}, {"id": "point2"}]', $response->getBody()->getContents());
    }

    public function testGetMemoryPointsFailure(): void
    {
        $this->mockHandler->append(new Response(500, [], '{"error": "Internal Server Error"}'));
        $this->expectException(CheshireCatApiException::class);
        $this->client->getMemoryPoints('collection1', 2, 0);
        $this->assertRequest('GET', '/memory/collections/collection1/points', ['query' => ['limit' => 2, 'offset' => 0]]);
    }

    public function testCreateMemoryPointSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"id": "point123"}'));
        $response = $this->client->createMemoryPoint('collection1', ['content' => 'test']);
        $this->assertRequest('POST', '/memory/collections/collection1/points', ['json' => ['content' => 'test']]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"id": "point123"}', $response->getBody()->getContents());
    }

    public function testCreateMemoryPointFailure(): void
    {
        $this->mockHandler->append(new Response(422, [], '{"error": "Validation Error"}'));
        $this->expectException(CheshireCatValidationException::class);
        $this->client->createMemoryPoint('collection1', ['content' => 'test']);
        $this->assertRequest('POST', '/memory/collections/collection1/points', ['json' => ['content' => 'test']]);
    }

    public function testDeleteMemoryPointSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"message": "Point deleted"}'));
        $response = $this->client->deleteMemoryPoint('collection1', 'point123');
        $this->assertRequest('DELETE', '/memory/collections/collection1/points/point123');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message": "Point deleted"}', $response->getBody()->getContents());
    }

    public function testDeleteMemoryPointFailure(): void
    {
        $this->mockHandler->append(new Response(404, [], '{"error": "Not Found"}'));
        $this->expectException(CheshireCatNotFoundException::class);
        $this->client->deleteMemoryPoint('collection1', 'point123');
        $this->assertRequest('DELETE', '/memory/collections/collection1/points/point123');
    }

    public function testGetAvailablePluginsSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '[{"id": "plugin1"}, {"id": "plugin2"}]'));
        $response = $this->client->getAvailablePlugins();
        $this->assertRequest('GET', '/plugins/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('[{"id": "plugin1"}, {"id": "plugin2"}]', $response->getBody()->getContents());
    }

    public function testGetAvailablePluginsFailure(): void
    {
        $this->mockHandler->append(new Response(500, [], '{"error": "Internal Server Error"}'));
        $this->expectException(CheshireCatApiException::class);
        $this->client->getAvailablePlugins();
        $this->assertRequest('GET', '/plugins/');
    }

    public function testInstallPluginSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"message": "Plugin installed"}'));
        $response = $this->client->installPlugin([['name' => 'file', 'contents' => 'filecontent']]);
        $this->assertRequest('POST', '/plugins/upload', ['multipart' => [['name' => 'file', 'contents' => 'filecontent']]]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message": "Plugin installed"}', $response->getBody()->getContents());
    }

    public function testInstallPluginFailure(): void
    {
        $this->mockHandler->append(new Response(422, [], '{"error": "Validation Error"}'));
        $this->expectException(CheshireCatValidationException::class);
        $this->client->installPlugin([['name' => 'file', 'contents' => 'filecontent']]);
        $this->assertRequest('POST', '/plugins/upload', ['multipart' => [['name' => 'file', 'contents' => 'filecontent']]]);
    }

    public function testTogglePluginSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"message": "Plugin toggled"}'));
        $response = $this->client->togglePlugin('plugin123');
        $this->assertRequest('PUT', '/plugins/toggle/plugin123');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message": "Plugin toggled"}', $response->getBody()->getContents());
    }

    public function testTogglePluginFailure(): void
    {
        $this->mockHandler->append(new Response(404, [], '{"error": "Not Found"}'));
        $this->expectException(CheshireCatNotFoundException::class);
        $this->client->togglePlugin('plugin123');
        $this->assertRequest('PUT', '/plugins/toggle/plugin123');
    }

    public function testUploadFileSuccess(): void
    {
        $this->mockHandler->append(new Response(200, [], '{"message": "File uploaded"}'));
        $filePath = 'tests/mocks/sample.txt';
        $fileName = 'sample.txt';
        $contentType = 'text/plain';

        // Create a dummy file for testing
        if (!file_exists('tests/mocks')) {
            mkdir('tests/mocks', 0777, true);
        }
        file_put_contents($filePath, 'This is a test file.');

        $response = $this->client->uploadFile($filePath, $fileName, $contentType);
        $this->assertRequest('POST', '/rabbithole/', ['multipart' => [
            ['name' => 'file', 'contents' => fopen($filePath, 'r'), 'filename' => $fileName],
            ['name' => 'chunk_size', 'contents' => 128],
            ['name' => 'metadata', 'contents' => json_encode([])],
        ]]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message": "File uploaded"}', $response->getBody()->getContents());

        // Clean up the dummy file
        unlink($filePath);
    }

    public function testUploadFileFailureFileNotFound(): void
    {
        $this->expectException(CheshireCatFileUploadException::class);
        $this->client->uploadFile('nonexistent.txt', 'nonexistent.txt', 'text/plain');
    }

    public function testUploadFileFailureNotReadable(): void
    {
        $this->expectException(CheshireCatFileUploadException::class);
        $filePath = 'tests/mocks/unreadable.txt';
        if (!file_exists('tests/mocks')) {
            mkdir('tests/mocks', 0777, true);
        }
        file_put_contents($filePath, 'This is a test file.');
        chmod($filePath, 0000); // Make the file unreadable

        $this->client->uploadFile($filePath, 'unreadable.txt', 'text/plain');
        chmod($filePath, 0644);
        unlink($filePath);
    }

    public function testUploadFileFailureApiError(): void
    {
        $this->mockHandler->append(new Response(500, [], '{"error": "Internal Server Error"}'));
        $this->expectException(CheshireCatApiException::class);
        $filePath = 'tests/mocks/sample.txt';
        $fileName = 'sample.txt';
        $contentType = 'text/plain';
        if (!file_exists('tests/mocks')) {
            mkdir('tests/mocks', 0777, true);
        }
        file_put_contents($filePath, 'This is a test file.');
        $this->client->uploadFile($filePath, $fileName, $contentType);
        $this->assertRequest('POST', '/rabbithole/', ['multipart' => [
            ['name' => 'file', 'contents' => fopen($filePath, 'r'), 'filename' => $fileName],
            ['name' => 'chunk_size', 'contents' => 128],
            ['name' => 'metadata', 'contents' => json_encode([])],
        ]]);
        unlink($filePath);
    }

    protected function assertRequest(string $method, string $uri, array $options = []): void
    {
        $request = $this->mockHandler->getLastRequest();
        $this->assertEquals($method, $request->getMethod());
        $this->assertEquals($this->baseUri . $uri, (string) $request->getUri());

        if (!empty($options)) {
            $body = $request->getBody()->getContents();
            if (isset($options['json'])) {
                $this->assertEquals($options['json'], json_decode($body, true));
            }
            if (isset($options['query'])) {
                $this->assertEquals($options['query'], $request->getUri()->getQuery());
            }
            if (isset($options['headers'])) {
                foreach ($options['headers'] as $name => $value) {
                    $this->assertEquals($value, $request->getHeaderLine($name));
                }
            }
            if (isset($options['multipart'])) {
                $this->assertStringContainsString('multipart/form-data', $request->getHeaderLine('Content-Type'));
                foreach ($options['multipart'] as $part) {
                    if(isset($part['contents'])){
                        if(is_resource($part['contents'])){
                            $this->assertStringContainsString($part['filename'], $body);
                        }else{
                            $this->assertStringContainsString($part['contents'], $body);
                        }
                    }
                }
            }
        }
    }
}
