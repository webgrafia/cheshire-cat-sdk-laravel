<?php
namespace CheshireCatSdk\Http\Clients;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
class CheshireCatClient
{
    protected $client;
    /**
     * CheshireCatClient constructor.
     * Initializes the HTTP Client with base URI and authorization headers.
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('cheshirecat.base_uri'),
            'headers' => ['Authorization' => 'Bearer ' . config('cheshirecat.api_key')],
        ]);
    }
    /**
     * Handles HTTP requests and catches any exceptions.
     *
     * @param string $method HTTP method (e.g. GET, POST).
     * @param string $uri URI path for the request.
     * @param array $options Optional request options.
     * @return mixed Response from the HTTP client or exception response.
     */
    private function handleRequest($method, $uri, $options = [])
    {
        try {
            return $this->client->request($method, $uri, $options);
        } catch (RequestException $e) {
            return $e->getResponse();
        }
    }
    /**
     * Get the status of the API.
     *
     * @return mixed API status response.
     */
    public function getStatus()
    {
        return $this->handleRequest('GET', '/');
    }
    /**
     * Send a message using the API.
     *
     * @param array $payload Message payload.
     * @return mixed API response.
     */
    public function sendMessage(array $payload)
    {
        return $this->handleRequest('POST', '/message', ['json' => $payload]);
    }
    /**
     * Obtain a token using user credentials.
     *
     * @param array $credentials User credentials.
     * @return mixed API response containing the token.
     */
    public function getToken(array $credentials)
    {
        return $this->handleRequest('POST', '/auth/token', ['json' => $credentials]);
    }
    /**
     * Retrieve a list of available permissions.
     *
     * @return mixed Permissions data.
     */
    public function getAvailablePermissions()
    {
        return $this->handleRequest('GET', '/auth/available-permissions');
    }
    /**
     * Create a new user.
     *
     * @param array $userData User data.
     * @return mixed API response.
     */
    public function createUser(array $userData)
    {
        return $this->handleRequest('POST', '/users/', ['json' => $userData]);
    }
    /**
     * Retrieve a list of users with pagination.
     *
     * @param int $skip Number of users to skip.
     * @param int $limit Number of users to retrieve.
     * @return mixed List of users.
     */
    public function getUsers($skip = 0, $limit = 100)
    {
        return $this->handleRequest('GET', "/users/?skip=$skip&limit=$limit");
    }
    /**
     * Retrieve details of a specific user.
     *
     * @param string $userId User ID.
     * @return mixed User details.
     */
    public function getUser($userId)
    {
        return $this->handleRequest('GET', "/users/{$userId}");
    }
    /**
     * Update details of an existing user.
     *
     * @param string $userId User ID.
     * @param array $userData Updated user data.
     * @return mixed API response.
     */
    public function updateUser($userId, array $userData)
    {
        return $this->handleRequest('PUT', "/users/{$userId}", ['json' => $userData]);
    }
    /**
     * Delete a user by ID.
     *
     * @param string $userId User ID.
     * @return mixed API response.
     */
    public function deleteUser($userId)
    {
        return $this->handleRequest('DELETE', "/users/{$userId}");
    }
    /**
     * Fetch application settings with optional search filtering.
     *
     * @param string|null $search Optional search query.
     * @return mixed Settings data.
     */
    public function getSettings($search = null)
    {
        return $this->handleRequest('GET', '/settings/', ['query' => ['search' => $search]]);
    }
    /**
     * Create a new application setting.
     *
     * @param array $settingData Setting data.
     * @return mixed API response.
     */
    public function createSetting(array $settingData)
    {
        return $this->handleRequest('POST', '/settings/', ['json' => $settingData]);
    }
    /**
     * Retrieve a specific setting by ID.
     *
     * @param string $settingId Setting ID.
     * @return mixed Setting details.
     */
    public function getSetting($settingId)
    {
        return $this->handleRequest('GET', "/settings/{$settingId}");
    }
    /**
     * Update an existing setting by ID.
     *
     * @param string $settingId Setting ID.
     * @param array $settingData Updated setting data.
     * @return mixed API response.
     */
    public function updateSetting($settingId, array $settingData)
    {
        return $this->handleRequest('PUT', "/settings/{$settingId}", ['json' => $settingData]);
    }
    /**
     * Delete a specific setting by ID.
     *
     * @param string $settingId Setting ID.
     * @return mixed API response.
     */
    public function deleteSetting($settingId)
    {
        return $this->handleRequest('DELETE', "/settings/{$settingId}");
    }
    /**
     * Retrieve memory points of a collection with pagination.
     *
     * @param string $collectionId Collection ID.
     * @param int $limit Maximum number of points to fetch.
     * @param int|null $offset Offset for pagination.
     * @return mixed List of memory points.
     */
    public function getMemoryPoints($collectionId, $limit = 100, $offset = null)
    {
        $query = ['limit' => $limit];
        if ($offset) {
            $query['offset'] = $offset;
        }
        return $this->handleRequest('GET', "/memory/collections/{$collectionId}/points", ['query' => $query]);
    }
    /**
     * Create a new memory point in a specific collection.
     *
     * @param string $collectionId Collection ID.
     * @param array $pointData Memory point data.
     * @return mixed API response.
     */
    public function createMemoryPoint($collectionId, array $pointData)
    {
        return $this->handleRequest('POST', "/memory/collections/{$collectionId}/points", ['json' => $pointData]);
    }
    /**
     * Delete a specific memory point by ID.
     *
     * @param string $collectionId Collection ID.
     * @param string $pointId Point ID.
     * @return mixed API response.
     */
    public function deleteMemoryPoint($collectionId, $pointId)
    {
        return $this->handleRequest('DELETE', "/memory/collections/{$collectionId}/points/{$pointId}");
    }
    /**
     * Retrieve a list of available plugins.
     *
     * @return mixed List of plugins.
     */
    public function getAvailablePlugins()
    {
        return $this->handleRequest('GET', '/plugins/');
    }
    /**
     * Install a new plugin via file upload.
     *
     * @param array $fileData File data for the plugin.
     * @return mixed API response.
     */
    public function installPlugin(array $fileData)
    {
        return $this->handleRequest('POST', '/plugins/upload', ['multipart' => $fileData]);
    }
    /**
     * Enable or disable a plugin by ID.
     *
     * @param string $pluginId Plugin ID.
     * @return mixed API response.
     */
    public function togglePlugin($pluginId)
    {
        return $this->handleRequest('PUT', "/plugins/toggle/{$pluginId}");
    }
}
