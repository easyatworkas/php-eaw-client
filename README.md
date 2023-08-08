# PHP easy@work API client

 * [Requirements](#requirements)
 * [Installation](#installation)
 * [Configuration](#configuration)
 * [Usage](#usage)
   * [As a shell](#usage-shell)
   * [In stand-alone script](#usage-standalone)
   * [As a CLI](#usage-cli)
 * [Examples](#examples)
   * [Using Models](#examples-models) 
   * [Using API client directly](#examples-client) 
   * [Logging](#examples-logging)
   * [Bringing it all together](#examples-empexport)
   * [Async requests](#async-requests)

## <a name="requirements"></a>Requirements

 * PHP 7.4+
 * Composer

## <a name="installation"></a>Installation

```sh
git clone https://github.com/easyatworkas/php-eaw-client.git
cd php-eaw-client
composer install
```

## <a name="configuration"></a>Configuration

Copy `.env.example` to `.env`, then edit `.env` and add your authentication details.

**Optional:** Add the bin directory to your PATH to invoke `e` from anywhere.

You can also authenticate by running `e userAuth <email> <password>` or `e clientAuth <client ID> <client secret>`. This will override any credentials in env, and store your session in `.auth.json` until it expires.

## <a name="usage"></a>Usage

### <a name="usage-shell"></a>As a shell

There are convenience scripts for Linux and Windows (`e` and `e.bat`) provided in the repo. You can optionally pass the path to a PHP file to execute it inside the shell environment.
```sh
./e <PHP file>
```

It is also possible to launch the shell directly through PHP: `php eaw.php`

### <a name="usage-standalone"></a>In a stand-alone script

Stand-alone scripts do not need to be launched via `./e`, but can instead be invoked directly; `php my_script.php`. All you need to do is include the bootstrapper, and you're ready to go:

```php
require('bootstrap/bootstrap.php');
```

### <a name="usage-cli"></a>As a CLI

This is a work in progress. POST data is still not passable.

```shell
./e create "/customers?name=Easy at Work AS&number=1"
./e read "/customers?order_by=id&direction=asc&per_page=1"
./e read /customers/1
./e update /customers/1?number=1337
./e delete /customers/1
```

### <a name="examples"></a>Examples

#### <a name="examples-models"></a>Using models
```php
// Create new customer.
$customer = Customer::newInstance([
    'name' => 'Easy at Work AS',
    'number' => 1,
])->save();

// Fetch customer with lowest ID.
$customer = Customer::newQuery()
    ->orderBy('id')
    ->direction('asc')
    ->perPage(1)
    ->getAll() // Returns a Paginator
    ->current(); // Return first item.

// Fetch customer 1.
$customer = Customer::get(1);

// Update customer 1's number.
Customer::get(1)->update([ 'number' => 1337 ]);

// Delete customer 1.
Customer::get(1)->delete();

// Handle paginated responses.
$employees = Employee::customer(1)->getAll();
foreach ($employees as $employee) {
    echo $employee->name, PHP_EOL;
}
```

#### <a name="examples-client"></a>Using API client directly:
```php
// Upload new profile picture.
eaw()->create('/users/1/picture', null, null, [ 'picture' => fopen('avatar.jpg', 'r') ]);

// Create new customer.
eaw()->create('/customers', null, [ 'name' => 'Easy at Work AS', 'number' => 1 ]);

// Fetch customer with lowest ID.
eaw()->read('/customers', [ 'order_by' => 'id', 'direction' => 'asc', 'per_page' => 1 ]);

// Fetch customer 1.
eaw()->read('/customers/1');

// Update customer 1's number.
eaw()->update('/customers/1', null, [ 'number' => 1337 ]);

// Delete customer 1.
eaw()->delete('/customers/1');

// Handle paginated responses.
$employees = eaw()->readPaginated('/customers/1/employees');
foreach ($employees as $employee) {
    echo $employee['name'], PHP_EOL;
}
```

#### <a name="examples-logging"></a>Logging

This project comes with [Monolog](https://github.com/Seldaek/monolog), and has a simple helper function to access and create new instances. New instances will be cloned from the default one, so any configuration changes you make to the default instance will be inherited by subsequent new ones.

```php
logger()->info('This is a message from the default logger.');

$myLogger = logger('awesomeness');
$myLogger->info('This is a message from my customer logger.');
```

Log messages support variables and colors.

```php
logger()->info('Found {lgreen}{num}{reset} employees.', [ 'num' => 1234 ]);
```

#### <a name="examples-empexport"></a>Bringing it all together

Here's a complete example that shows how to fetch all employees from all customers, then print their details to the console in a table.

```php
$table = [];

logger()->info('Fetching customers...');

$customers = Customer::newQuery()
    ->orderBy('number')
    ->direction('asc')
    ->getAll();

foreach ($customers as $customer) {
    logger()->info($customer->name);
    logger()->info('Fetching employees...');

    $employees = $customer->employees()
        ->includeInactive(true)
        ->orderBy('name')
        ->direction('asc')
        ->getAll();

    foreach ($employees as $employee) {
        $table[] = [
            'cust_number' => $customer->number,
            'cust_name' => $customer->name,
            'number' => $employee->number,
            'name' => $employee->name,
            'phone' => $employee->phone,
            'email' => $employee->email,
            'from' => $employee->from,
            'to' => $employee->to,
        ];
    }
}

logger()->info('Done.');

tabelize($table, [
    'cust_number' => 'c#',
    'cust_name' => 'location',
    'number' => 'e#'
]);
```

#### <a name="async-requests"></a>Async requests

It is possible to run multiple requests in parallel to increase the efficiency of your code.

```php
$employees = eaw()->readPaginated('/customers/1/employees');

foreach ($employees as $employee) {
    eaw()->updateAsync("/customers/1/employees/{$employee['id']}/properties/cf_flexi", [ 'value' => false ])
        .then(function (array $response) {
            logger()->info("Flexi time for employee #{$employee['number']} has been disabled.");
        });
}

eaw()->execute();
```

Keep in mind that we enfore a rate-limit on traffic to the API, and even though the client will retry throttled requests automatically, you should try to avoid it all toghether; especially when writing asynchrounous code, since the first throttled request won't stop subsequent requests from being made immediately.
