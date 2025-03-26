<?php

namespace CheshireCatSdk\Http\Clients;

use CheshireCatSdk\Exceptions\CheshireCatApiException;
use CheshireCatSdk\Exceptions\CheshireCatAuthenticationException;
use CheshireCatSdk\Exceptions\CheshireCatNotFoundException;
use CheshireCatSdk\Exceptions\CheshireCatValidationException;
use CheshireCatSdk\Exceptions\CheshireCatFileUploadException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;

class CheshireCatClient
{
    protected Client $client;
    protected string $baseUri;
    protected string $apiKey;

    public function __construct(string $baseUri = 'http://localhost:1865/', string $apiKey = '')
    {
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
        ]);
    }

    // ... (rest of your methods) ...
    private function handleRequest($method, $uri, $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);
            return $response;
        } catch (ConnectException $e) {
            throw new CheshireCatApiException('API connection failed', 0, $e);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 500;

            switch ($statusCode) {
                case 401:
                    throw new CheshireCatAuthenticationException('Authentication failed', $statusCode, $e);
                case 404:
                    throw new CheshireCatNotFoundException('Resource not found', $statusCode, $e);
                case 422:
                    throw new CheshireCatValidationException('Validation error', $statusCode, $e);
                default:
                    throw new CheshireCatApiException('API request failed', $statusCode, $e);
            }
        }
    }
    public function getStatus()
    {
        return $this->handleRequest('GET', '/');
    }
    public function sendMessage(array $payload)
    {
        return $this->handleRequest('POST', '/message', ['json' => $payload]);
    }
    public function getToken(array $payload)
    {
        return $this->handleRequest('POST', '/auth/token', ['json' => $payload]);
    }
    public function getAvailablePermissions()
    {
        return $this->handleRequest('GET', '/auth/available-permissions');
    }
    public function createUser(array $payload)
    {
        return $this->handleRequest('POST', '/users/', ['json' => $payload]);
    }
    public function getUsers(int $skip, int $limit)
    {
        return $this->handleRequest('GET', '/users/', ['query' => ['skip' => $skip, 'limit' => $limit]]);
    }
    public function getUser(string $userId)
    {
        return $this->handleRequest('GET', "/users/{$userId}");
    }
    public function updateUser(string $userId, array $payload)
    {
        return $this->handleRequest('PUT', "/users/{$userId}", ['json' => $payload]);
    }
    public function deleteUser(string $userId)
    {
        return $this->handleRequest('DELETE', "/users/{$userId}");
    }
    public function getSettings()
    {
        return $this->handleRequest('GET', '/settings/');
    }
    public function createSetting(array $payload)
    {
        return $this->handleRequest('POST', '/settings/', ['json' => $payload]);
    }
    public function getSetting(string $settingId)
    {
        return $this->handleRequest('GET', "/settings/{$settingId}");
    }
    public function updateSetting(string $settingId, array $payload)
    {
        return $this->handleRequest('PUT', "/settings/{$settingId}", ['json' => $payload]);
    }
    public function deleteSetting(string $settingId)
    {
        return $this->handleRequest('DELETE', "/settings/{$settingId}");
    }
    public function getMemoryPoints(string $collectionId, int $limit, int $offset)
    {
        return $this->handleRequest('GET', "/memory/collections/{$collectionId}/points", ['query' => ['limit' => $limit, 'offset' => $offset]]);
    }
    public function createMemoryPoint(string $collectionId, array $payload)
    {
        return $this->handleRequest('POST', "/memory/collections/{$collectionId}/points", ['json' => $payload]);
    }
    public function deleteMemoryPoint(string $collectionId, string $pointId)
    {
        return $this->handleRequest('DELETE', "/memory/collections/{$collectionId}/points/{$pointId}");
    }
    public function getAvailablePlugins()
    {
        return $this->handleRequest('GET', '/plugins/');
    }
    public function installPlugin(array $payload)
    {
        return $this->handleRequest('POST', '/plugins/upload', ['multipart' => $payload]);
    }
    public function togglePlugin(string $pluginId)
    {
        return $this->handleRequest('PUT', "/plugins/toggle/{$pluginId}");
    }
    public function uploadFile(string $filePath, string $fileName, string $contentType, array $metadata = [], int $chunkSize = 128)
    {
        if (!file_exists($filePath)) {
            throw new CheshireCatFileUploadException("File does not exist: {$filePath}");
        }
        if (!is_readable($filePath)) {
            throw new CheshireCatFileUploadException("File is not readable: {$filePath}");
        }
        $file = fopen($filePath, 'r');
        $payload = [
            [
                'name' => 'file',
                'contents' => $file,
                'filename' => $fileName,
            ],
            [
                'name' => 'chunk_size',
                'contents' => $chunkSize,
            ],
            [
                'name' => 'metadata',
                'contents' => json_encode($metadata),
            ],
        ];
        return $this->handleRequest('POST', '/rabbithole/', ['multipart' => $payload]);
    }
}