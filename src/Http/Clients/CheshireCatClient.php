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
            'timeout' => 60,
        ]);
    }

    /**
     * Handles HTTP requests and catches any exceptions.
     *
     * @param string $method  HTTP method (e.g. GET, POST).
     * @param string $uri     URI path for the request.
     * @param array  $options Optional request options.
     *
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
     * @param array $headers Additional headers to include in the request.
     *
     * @return mixed API response.
     */
    public function sendMessage(array $payload, array $headers = [])
    {
        $options = ['json' => $payload];

        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        return $this->handleRequest('POST', '/message', $options);
    }

    /**
     * Obtain a token using user credentials.
     *
     * @param array $credentials User credentials.
     *
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
     *
     * @return mixed API response.
     */
    public function createUser(array $userData)
    {
        return $this->handleRequest('POST', '/users/', ['json' => $userData]);
    }

    /**
     * Retrieve a list of users with pagination.
     *
     * @param int $skip  Number of users to skip.
     * @param int $limit Number of users to retrieve.
     *
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
     *
     * @return mixed User details.
     */
    public function getUser($userId)
    {
        return $this->handleRequest('GET', "/users/{$userId}");
    }

    /**
     * Update details of an existing user.
     *
     * @param string $userId   User ID.
     * @param array  $userData Updated user data.
     *
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
     *
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
     *
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
     *
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
     *
     * @return mixed Setting details.
     */
    public function getSetting($settingId)
    {
        return $this->handleRequest('GET', "/settings/{$settingId}");
    }

    /**
     * Update an existing setting by ID.
     *
     * @param string $settingId   Setting ID.
     * @param array  $settingData Updated setting data.
     *
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
     *
     * @return mixed API response.
     */
    public function deleteSetting($settingId)
    {
        return $this->handleRequest('DELETE', "/settings/{$settingId}");
    }

    /**
     * Retrieve memory points of a collection with pagination.
     *
     * @param string   $collectionId Collection ID.
     * @param int      $limit        Maximum number of points to fetch.
     * @param int|null $offset       Offset for pagination.
     *
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
     * @param array  $pointData    Memory point data.
     *
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
     * @param string $pointId      Point ID.
     *
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
     *
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
     *
     * @return mixed API response.
     */
    public function togglePlugin($pluginId)
    {
        return $this->handleRequest('PUT', "/plugins/toggle/{$pluginId}");
    }

    /**
     * Get the list of the Large Language Models.
     *
     * @return mixed
     */
    public function getLlmsSettings()
    {
        return $this->handleRequest('GET', '/llm/settings');
    }

    /**
     * Get settings and schema of the specified Large Language Model.
     *
     * @param string $languageModelName
     *
     * @return mixed
     */
    public function getLlmSettings(string $languageModelName)
    {
        return $this->handleRequest('GET', "/llm/settings/{$languageModelName}");
    }

    /**
     * Upsert the Large Language Model setting.
     *
     * @param string $languageModelName
     * @param array  $payload
     *
     * @return mixed
     */
    public function upsertLlmSetting(string $languageModelName, array $payload)
    {
        return $this->handleRequest('PUT', "/llm/settings/{$languageModelName}", ['json' => $payload]);
    }

    /**
     * Get the list of the Embedders.
     *
     * @return mixed
     */
    public function getEmbeddersSettings()
    {
        return $this->handleRequest('GET', '/embedder/settings');
    }

    /**
     * Get settings and schema of the specified Embedder.
     *
     * @param string $languageEmbedderName
     *
     * @return mixed
     */
    public function getEmbedderSettings(string $languageEmbedderName)
    {
        return $this->handleRequest('GET', "/embedder/settings/{$languageEmbedderName}");
    }

    /**
     * Upsert the Embedder setting.
     *
     * @param string $languageEmbedderName
     * @param array  $payload
     *
     * @return mixed
     */
    public function upsertEmbedderSetting(string $languageEmbedderName, array $payload)
    {
        return $this->handleRequest('PUT', "/embedder/settings/{$languageEmbedderName}", ['json' => $payload]);
    }

    /**
     * Install a new plugin from registry.
     *
     * @param array $payload
     *
     * @return mixed
     */
    public function installPluginFromRegistry(array $payload)
    {
        return $this->handleRequest('POST', '/plugins/upload/registry', ['json' => $payload]);
    }

    /**
     * Returns the settings of all the plugins.
     *
     * @return mixed
     */
    public function getPluginsSettings()
    {
        return $this->handleRequest('GET', '/plugins/settings');
    }

    /**
     * Returns the settings of a specific plugin.
     *
     * @param string $pluginId
     *
     * @return mixed
     */
    public function getPluginSettings(string $pluginId)
    {
        return $this->handleRequest('GET', "/plugins/settings/{$pluginId}");
    }

    /**
     * Updates the settings of a specific plugin.
     *
     * @param string $pluginId
     * @param array  $payload
     *
     * @return mixed
     */
    public function upsertPluginSettings(string $pluginId, array $payload)
    {
        return $this->handleRequest('PUT', "/plugins/settings/{$pluginId}", ['json' => $payload]);
    }

    /**
     * Returns information on a single plugin.
     *
     * @param string $pluginId
     *
     * @return mixed
     */
    public function getPluginDetails(string $pluginId)
    {
        return $this->handleRequest('GET', "/plugins/{$pluginId}");
    }

    /**
     * Physically remove plugin.
     *
     * @param string $pluginId
     *
     * @return mixed
     */
    public function deletePlugin(string $pluginId)
    {
        return $this->handleRequest('DELETE', "/plugins/{$pluginId}");
    }

    /**
     * Search k memories similar to given text.
     *
     * @param string $text
     * @param int    $k
     *
     * @return mixed
     */
    public function recallMemoryPointsFromText(string $text, int $k = 100)
    {
        return $this->handleRequest('GET', '/memory/recall', ['query' => ['text' => $text, 'k' => $k]]);
    }

    /**
     * Search k memories similar to given text with specified metadata criteria.
     *
     * @param array $payload
     *
     * @return mixed
     */
    public function recallMemoryPoints(array $payload)
    {
        return $this->handleRequest('POST', '/memory/recall', ['json' => $payload]);
    }

    /**
     * Delete points in memory by filter.
     *
     * @param string $collectionId
     * @param array  $metadata
     *
     * @return mixed
     */
    public function deleteMemoryPointsByMetadata(string $collectionId, array $metadata = [])
    {
        return $this->handleRequest('DELETE', "/memory/collections/{$collectionId}/points", ['json' => $metadata]);
    }

    /**
     * Retrieve all the points from a single collection.
     *
     * @param string      $collectionId
     * @param int         $limit
     * @param string|null $offset
     *
     * @return mixed
     */
    public function getPointsInCollection(string $collectionId, int $limit = 100, string $offset = null)
    {
        $query = ['limit' => $limit];
        if ($offset !== null) {
            $query['offset'] = $offset;
        }
        return $this->handleRequest('GET', "/memory/collections/{$collectionId}/points", ['query' => $query]);
    }

    /**
     * Edit a point in memory.
     *
     * @param string $collectionId
     * @param string $pointId
     * @param array  $pointData
     *
     * @return mixed
     */
    public function editMemoryPoint(string $collectionId, string $pointId, array $pointData)
    {
        return $this->handleRequest('PUT', "/memory/collections/{$collectionId}/points/{$pointId}", ['json' => $pointData]);
    }

    /**
     * Get list of available collections.
     *
     * @return mixed
     */
    public function getCollections()
    {
        return $this->handleRequest('GET', '/memory/collections');
    }

    /**
     * Delete and create all collections.
     *
     * @return mixed
     */
    public function wipeCollections()
    {
        return $this->handleRequest('DELETE', '/memory/collections');
    }

    /**
     * Delete and recreate a collection.
     *
     * @param string $collectionId
     *
     * @return mixed
     */
    public function wipeSingleCollection(string $collectionId)
    {
        return $this->handleRequest('DELETE', "/memory/collections/{$collectionId}");
    }

    /**
     * Get the specified user's conversation history from working memory.
     *
     * @return mixed
     */
    public function getConversationHistory()
    {
        return $this->handleRequest('GET', '/memory/conversation_history');
    }

    /**
     * Delete the specified user's conversation history from working memory.
     *
     * @return mixed
     */
    public function wipeConversationHistory()
    {
        return $this->handleRequest('DELETE', '/memory/conversation_history');
    }

    /**
     * Carica un file al server tramite l'endpoint '/rabbithole/'.
     *
     * @param string $filePath   Il percorso del file da caricare.
     * @param string $fileName   Il nome del file.
     * @param string $contentType Il tipo MIME del file.
     * @param array  $metadata   Metadati aggiuntivi per il file (opzionale).
     * @param int    $chunkSize  Dimensione di chunk (opzionale).
     * @return mixed Risposta dall'API.
     *
     * @throws \Exception Se il file non esiste o non è leggibile.
     */
    public function uploadFile(string $filePath, string $fileName, string $contentType, array $metadata = [], int $chunkSize = 128)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("Il file non esiste o non è leggibile: {$filePath}");
        }

        // Prepara i dati multipart.
        $fileData = [
            [
                'name' => 'file',
                'contents' => fopen($filePath, 'r'),
                'filename' => $fileName,
                'headers' => [
                    'Content-Type' => $contentType,
                ],
            ],
            [
                'name' => 'chunk_size',
                'contents' => $chunkSize,
            ],
            [
                'name' => 'metadata',
                'contents' => json_encode($metadata), // Metadata JSON-encoded
            ],
        ];

        // Esegui la richiesta POST
        return $this->handleRequest('POST', '/rabbithole/', ['multipart' => $fileData]);
    }


    /**
     * Batch upload multiple files containing text (.txt, .md, .pdf, etc.).
     *
     * @param array $fileData
     *
     * @return mixed
     */
    public function uploadFiles(array $fileData)
    {
        return $this->handleRequest('POST', '/rabbithole/batch', ['multipart' => $fileData]);
    }

    /**
     * Upload a url.
     *
     * @param array $payload
     *
     * @return mixed
     */
    public function uploadUrl(array $payload)
    {
        return $this->handleRequest('POST', '/rabbithole/web', ['json' => $payload]);
    }

    /**
     * Upload a memory json file to the cat memory.
     *
     * @param array $fileData
     *
     * @return mixed
     */
    public function uploadMemory(array $fileData)
    {
        return $this->handleRequest('POST', '/rabbithole/memory', ['multipart' => $fileData]);
    }

    /**
     * Retrieve the allowed mimetypes that can be ingested by the Rabbit Hole.
     *
     * @return mixed
     */
    public function getAllowedMimetypes()
    {
        return $this->handleRequest('GET', '/rabbithole/allowed-mimetypes');
    }

    /**
     * Get the list of the AuthHandlers.
     *
     * @return mixed
     */
    public function getAuthHandlerSettings()
    {
        return $this->handleRequest('GET', '/auth_handler/settings');
    }

    /**
     * Get the settings of a specific AuthHandler.
     *
     * @param string $authHandlerName
     *
     * @return mixed
     */
    public function getAuthHandlerSetting(string $authHandlerName)
    {
        return $this->handleRequest('GET', "/auth_handler/settings/{$authHandlerName}");
    }

    /**
     * Upsert the settings of a specific AuthHandler.
     *
     * @param string $authHandlerName
     * @param array  $payload
     *
     * @return mixed
     */
    public function upsertAuthenticatorSetting(string $authHandlerName, array $payload)
    {
        return $this->handleRequest('PUT', "/auth_handler/settings/{$authHandlerName}", ['json' => $payload]);
    }
}
