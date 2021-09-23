# PHP easy@work API client

## Installation

```sh
git clone https://github.com/easyatworkas/php-eaw-client.git .
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
eaw()->read('/customers', [ 'order_by' => 'id', 'direction' => 'asc', 'per_page' => 1 ]);
eaw()->read('/customers/1');
eaw()->update('/customers/1', null, [ 'number' => 1337 ]);
eaw()->delete('/customers/1');
```

### As a CLI

This is a work in progress. URL parameters and POST data is still not passable.

```shell
./e read /customers
./e read /customers/1
./e update /customers/1
./e delete /customers/1
```
