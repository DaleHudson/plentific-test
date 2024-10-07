# Plentific Remote Package

This package provides a client for interacting with the Reqres API, including methods for retrieving and creating users.

## Installation

You can install the package via Composer:

```bash
composer require plentific/remote
```

## Usage

To use the ReqresRemote class, you need to initialize it with the guzzle http client:

```php
use GuzzleHttp\Client;
use Plentific\Remote\ReqresRemote;

$client = new Client(['base_uri' => 'https://reqres.in/api/']);
$reqresRemote = new ReqresRemote($client);
```

## Getting a User by ID

You can retrieve a user by their ID using the getUserByIdMethod:

```php
use Plentific\DTOs\UserDTO;

$user = $reqresRemote->getUserById(1);
echo $user->getFirstName(); // Output: "George"
```

## Getting  Paginated Users

You can retrieve a paginated list of users using the getPaginatedUsers method:

```php
$users = $reqresRemote->getPaginatedUsers(1);
foreach ($users as $user) {
    echo $user->getFirstName();
}
```

## Creating a User

You can create a user using the createUser method. This method requires a validator instance and
an array of user data:

```php
use Plentific\Validator\Validator;

$validator = new Validator();
$userId = $reqresRemote->createUser($validator, [
    'name' => 'John Doe',
    'job' => 'Developer'
]);
echo $userId; // Output: 100
```

### Exception Handling

The ReqresRemote class throws ApiException for any errors that occur during API requests. You should handle these exceptions in your application:

```php
use Plentific\Exceptions\ApiException;

try {
    $user = $reqresRemote->getUserById(1);
} catch (ApiException $e) {
    echo 'Error: ' . $e->getMessage();
}
```

### Running Tests

You can run the tests using PHPUnit:

```bash
./vendor/bin/phpunit tests
```