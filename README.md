# PHP easy@work API client

## Installation

```sh
git clone https://github.com/easyatworkas/php-eaw-client.git .
composer install
```

## Usage
Open PsySH:
```sh
php sh.php
```

Then have at it :)
```php
eaw()->userAuth('your@email.com', 'your!passw0rd');
eaw()->read('/customers', [ 'order_by' => 'id', 'direction' => 'asc', 'per_page' => 1 ]);
eaw()->read('/customers/1');
eaw()->update('/customers/1', null, [ 'number' => 1337 ]);
eaw()->delete('/customers/1');
```
