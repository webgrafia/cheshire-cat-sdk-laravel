# Cheshire Cat SDK for Laravel

![Cheshire Cat Logo](assets/logo.png)
Laravel SDK for interacting with [Cheshire Cat AI](https://github.com/cheshire-cat-ai/) API, providing seamless integration with endpoints for messages, user management, settings, memory, plugins, and more.

---

## API Reference and Versioning

This SDK is built to interact with the Cheshire Cat API. For a comprehensive list of all available endpoints, their parameters, and expected responses, please refer to the [OpenAPI specification file](docs/openapi.json).

**Version Compatibility:** The `openapi.json` file also indicates the specific version of the Cheshire Cat API that this SDK is designed to work with. This ensures compatibility and helps you understand the features and functionalities supported by the SDK.

**Current API Version:** 1.9.0 (as per openapi.json)

---

## Installation

1. Install the package via Composer:

   ```bash
   composer require webgrafia/cheshire-cat-sdk-laravel
   ```

2. Publish the configuration file:

   ```bash
   php artisan vendor:publish --tag=config --provider="CheshireCatSdk\CheshireCatServiceProvider"
   ```

3. Update `.env` with Cheshire Cat API credentials:

   ```env
   CHESHIRE_CAT_BASE_URI=http://localhost:1865/
   CHESHIRE_CAT_WS_BASE_URI=ws://localhost:1865/ws
   CHESHIRE_CAT_API_KEY=your_api_key_here
   ```

---

## Configuration

The published configuration file is located at `config/cheshirecat.php`:

```php
return [
    'base_uri' => env('CHESHIRE_CAT_BASE_URI', 'http://localhost:1865/'),
    'ws_base_uri' => env('CHESHIRE_CAT_WS_BASE_URI', 'ws://localhost:1865/ws'),
    'api_key' => env('CHESHIRE_CAT_API_KEY'),
];
```

---

## Usage

Use the `CheshireCat` Facade or the `CheshireCat` class directly.


## Examples


### Methods


#### 1. Status Check
```php
use CheshireCatSdk\Facades\CheshireCatFacade as CheshireCat;

$response = CheshireCat::status();

if ($response->getStatusCode() === 200) {
    echo "API is up and running!";
}
```

#### 2. Send a Message
```php
$response = CheshireCat::message('Hello, Cheshire Cat!');
$data = json_decode($response->getBody(), true);

echo $data['text'];
```

#### 3. Get Available Permissions
```php
$response = CheshireCat::getAvailablePermissions();
$permissions = json_decode($response->getBody(), true);

print_r($permissions);
```

#### 4. User Management
- **Create a User**
  ```php
  $response = CheshireCat::createUser([
      'username' => 'testuser',
      'password' => 'securepassword',
  ]);
  $user = json_decode($response->getBody(), true);

  echo $user['id'];
  ```

- **Get Users**
  ```php
  $response = CheshireCat::getUsers(0, 10);
  $users = json_decode($response->getBody(), true);

  print_r($users);
  ```

- **Update a User**
  ```php
  $response = CheshireCat::updateUser('user_id', [
      'username' => 'updateduser',
  ]);
  echo $response->getStatusCode();
  ```

- **Delete a User**
  ```php
  $response = CheshireCat::deleteUser('user_id');
  echo $response->getStatusCode();
  ```

#### 5. Manage Settings
- **Get All Settings**
  ```php
  $response = CheshireCat::getSettings();
  $settings = json_decode($response->getBody(), true);

  print_r($settings);
  ```

- **Create a Setting**
  ```php
  $response = CheshireCat::createSetting([
      'name' => 'new_setting',
      'value' => 'some_value',
  ]);
  echo $response->getStatusCode();
  ```

- **Update a Setting**
  ```php
  $response = CheshireCat::updateSetting('setting_id', [
      'value' => 'updated_value',
  ]);
  echo $response->getStatusCode();
  ```

- **Delete a Setting**
  ```php
  $response = CheshireCat::deleteSetting('setting_id');
  echo $response->getStatusCode();
  ```

#### 6. Memory Management
- **Get Memory Points**
  ```php
  $response = CheshireCat::getMemoryPoints('collection_id');
  $points = json_decode($response->getBody(), true);

  print_r($points);
  ```

- **Create a Memory Point**
  ```php
  $response = CheshireCat::createMemoryPoint('collection_id', [
      'content' => 'This is a memory point.',
  ]);
  echo $response->getStatusCode();
  ```

- **Delete a Memory Point**
  ```php
  $response = CheshireCat::deleteMemoryPoint('collection_id', 'point_id');
  echo $response->getStatusCode();
  ```

#### 7. Plugin Management
- **Get Plugins**
  ```php
  $response = CheshireCat::getAvailablePlugins();
  $plugins = json_decode($response->getBody(), true);

  print_r($plugins);
  ```

- **Install a Plugin**
  ```php
  $file = fopen('/path/to/plugin.zip', 'r');

  $response = CheshireCat::installPlugin([
      [
          'name' => 'file',
          'contents' => $file,
      ],
  ]);
  echo $response->getStatusCode();
  ```

- **Toggle a Plugin**
  ```php
  $response = CheshireCat::togglePlugin('plugin_id');
  echo $response->getStatusCode();
  ```

---
### WebSocket Connection

The SDK supports WebSocket connections for real-time communication with the Cheshire Cat AI server.


#### WebSocket Basic Usage Example

```php
use CheshireCatSdk\Facades\CheshireCatFacade as CheshireCat;
$payload = ['text' => 'Hello, who are you?'];
        CheshireCat::sendMessageViaWebSocket($payload);

        // Ciclo per ricevere più messaggi
        while (true) {
            // Ricevi la risposta
            $response = CheshireCat::wsClient()->receive();
            $response = json_decode($response, true);

            if($response["type"] == "chat"){
                echo $response["text"];
                break;
            }
        }

        // Chiudi la connessione WebSocket
        CheshireCat::closeWebSocketConnection();
```

---
### Rabbit Hole File Upload
Upload a file to the API via the `/rabbithole/` endpoint.

```php
use CheshireCatSdk\Facades\CheshireCatFacade as CheshireCat;

$filePath = 'tests/mocks/sample.pdf';
$fileName = 'sample.pdf';
$contentType = 'application/pdf';

$metadata = [
    "source" => "sample.pdf",
    "title" => "Test title",
    "author" => "Test author",
    "year" => 2020,
];

$response = CheshireCat::uploadFile($filePath, $fileName, $contentType, $metadata);

if ($response->getStatusCode() === 200) {
    echo "File uploaded successfully!";
}
```

**Parameters**:
- `$filePath` - The path to the file to be uploaded.
- `$fileName` - The name of the file.
- `$contentType` - MIME type of the file (e.g., `application/pdf`).
- `$metadata` - Associative array containing optional metadata related to the file.

**Response**:
- Returns an HTTP response object containing the details of the API's response.

---

## Default Routes

The SDK provides 2 default route `/meow/status` and `/meow/hello` that can be used to check the status of the Cheshire Cat API.

### Usage

To check the status of the API, simply navigate to `http://your-app-domain/meow/status` in your browser.
To say Hello to Cheshire simply navigate to `http://your-app-domain/meow/hello` in your browser.

This routes will return a message indicating whether the API connection was successful or not, along with the status response or any error messages.

## Custom route for Testing

example of route in web.php for testing
```php
use Illuminate\Support\Facades\Route;
use CheshireCatSdk\Facades\CheshireCatFacade as CheshireCat;

Route::get('/meow_connection', function () {
    try {
        // Try to get the status of the Cheshire Cat API
        $statusResponse = CheshireCat::getStatus();

        // Check if the status response is successful
        if ($statusResponse->getStatusCode() === 200) {
            echo "Cheshire Cat API connection successful!<br>";
            echo "Status Response: " . $statusResponse->getBody()->getContents();
        } else {
            echo "Cheshire Cat API connection failed!<br>";
            echo "Status Response: " . $statusResponse->getBody()->getContents();
        }
    } catch (\Exception $e) {
        echo "Cheshire Cat API connection failed!<br>";
        echo "Error: " . $e->getMessage();
    }
});
```

---

## Contributing

Feel free to fork this repository and submit pull requests.

---

## License

This package is open-source software licensed under the [GNU GENERAL PUBLIC LICENSE](LICENSE).

