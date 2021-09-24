# PHP easy@work API client

## Installation

```sh
git clone https://github.com/easyatworkas/php-eaw-client.git
cd php-eaw-client
composer install
```

## Configuration

Copy `.env.example` to `.env`, then edit `.env` and add your authentication details.

## Usage

### As a shell
```sh
./e
```

Then have at it :)
```php
eaw()->create('/users/1/picture', null, null, [ 'picture' => fopen('avatar.jpg', 'r') ]);
eaw()->create('/customers', null, [ 'name' => 'Easy at Work AS', 'number' => 1 ]);
eaw()->read('/customers', [ 'order_by' => 'id', 'direction' => 'asc', 'per_page' => 1 ]);
eaw()->read('/customers/1');
eaw()->update('/customers/1', null, [ 'number' => 1337 ]);
eaw()->delete('/customers/1');
```

Working with paginated responses is also easy, using the Paginator iterator. It will automatically load pages as required.
```php
$employees = eaw()->readPaginated('/customers/1/employees');

foreach ($employees as $employee) {
    echo $employee['name'], PHP_EOL;
}
```

You can execute PHP scripts that utilize this functionality by passing them in like this:

```sh
./e my_script.php
```

### In a stand-alone script

Stand-alone scripts do not need to be launched via `./e`, but can instead be invoked directly; `php my_script.php`. All you need to do is include the bootstrapper, and you're ready to go:

```php
require('bootstrap/bootstrap.php');

$customers = eaw()->readPaginated('/customers', [ 'order_by' => 'name' ]);

foreach ($customers as $customer) {
    echo $customer['name'], PHP_EOL;
}
```

### As a CLI

This is a work in progress. POST data is still not passable.

```shell
./e create "/customers?name=Easy at Work AS&number=1"
./e read "/customers?order_by=id&direction=asc&per_page=1"
./e read /customers/1
./e update /customers/1?number=1337
./e delete /customers/1
```
