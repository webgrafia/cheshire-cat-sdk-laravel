# Cheshire Cat SDK for Laravel

![Cheshire Cat AI](https://cheshirecat.ai/wp-content/uploads/2023/10/Logo-Cheshire-Cat.svg)

Laravel SDK for interacting with Cheshire Cat AI API, providing seamless integration with endpoints for messages, user management, settings, memory, plugins, and more.

---

## Installation

1. Install the package via Composer:

   ```bash
   composer require username/cheshire-cat-sdk-laravel
   ```

2. Publish the configuration file:

   ```bash
   php artisan vendor:publish --tag=config --provider="CheshireCatSdk\CheshireCatServiceProvider"
   ```

3. Update `.env` with Cheshire Cat API credentials:

   ```env
   CHESHIRE_CAT_BASE_URI=https://api.cheshirecat.ai
   CHESHIRE_CAT_API_KEY=your_api_key_here
   ```

---

## Configuration

The published configuration file is located at `config/cheshirecat.php`:

```php
return [
    'base_uri' => env('CHESHIRE_CAT_BASE_URI', 'https://api.cheshirecat.ai'),
    'api_key' => env('CHESHIRE_CAT_API_KEY'),
];
```

---

## Usage

Use the `CheshireCat` Facade or the `CheshireCat` class directly.

### Examples

#### 1. Status Check
```php
use CheshireCatSdk\Facades\CheshireCat;

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

## Testing

Run tests using PHPUnit:

```bash
vendor/bin/phpunit
```

---

## Contributing

Feel free to fork this repository and submit pull requests.

---

## License

This package is open-source software licensed under the [MIT license](LICENSE).

