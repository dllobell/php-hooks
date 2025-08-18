# PHP Hooks

<p>
<a href="https://packagist.org/packages/dllobell/hooks"><img src="https://img.shields.io/packagist/dt/dllobell/hooks" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/dllobell/hooks"><img src="https://img.shields.io/packagist/v/dllobell/hooks" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/dllobell/hooks"><img src="https://img.shields.io/packagist/l/dllobell/hooks" alt="License"></a>
<a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php" alt="PHP Minimum Version"></a>
</p>

Lightweight, framework-agnostic hooks system for PHP.

> [!WARNING]
> This package is a work in progress, do not use this unless for fun until the first release

## Installation

Install via Composer:

```bash
composer require dllobell/hooks
```

## Usage

### Quick start

```php
use Dllobell\Hooks\Hooks;

$hooks = Hooks::create();

// Register a handler
$hooks->register('user.created', function (array $user): void {
	// ...do something
});

// Call the hook (handlers receive the same arguments)
$hooks->call('user.created', ['id' => 1, 'name' => 'David']);
```

### Unregistering handlers

You can remove a handler either by calling the unregister closure returned by `register()`, or by calling `unregister()` with the same closure reference.

```php
use Dllobell\Hooks\Hooks;

$hooks = Hooks::create();

// Option A: use the returned unregister closure
$unregister = $hooks->register('sync.completed', function (): void {
	// ...
});

$unregister(); // handler will no longer be called

// Option B: call unregister with the same closure
$handler = function (): void {
	// ...
};
$hooks->register('sync.completed', $handler);
$hooks->unregister('sync.completed', $handler);
```

### One-time handlers

Use `registerOnce()` to run a handler only on the first call of a hook.

```php
use Dllobell\Hooks\Hooks;

$hooks = Hooks::create();

$hooks->registerOnce('cache.warm', function (): void {
	// Runs only the first time 'cache.warm' is called
});

$hooks->call('cache.warm'); // handler will be called
$hooks->call('cache.warm'); // handler will not be called
```

### Global beforeEach callbacks

Run logic before every hook call (e.g., logging, timing, tracing). The callback receives the hook name and the arguments.

```php
use Dllobell\Hooks\Hooks;

$hooks = Hooks::create();

$hooks->beforeEach(function (string $name, ...$args): void {
	// e.g. start timing, log, etc.
});

$hooks->register('task.run', function (int $id): void {
	// ...
});

$hooks->call('task.run', 42);
```

### Global afterEach callbacks

Run logic after every hook call. The callback receives the hook name and the arguments.

```php
use Dllobell\Hooks\Hooks;

$hooks = Hooks::create();

$hooks->afterEach(function (string $name, ...$args): void {
	// e.g. stop timing, log, etc.
});

$hooks->register('task.run', function (int $id): void {
	// ...
});

$hooks->call('task.run', 42);
```

### Per-hook before callbacks

Run logic before handlers for a specific hook. The callback receives the same arguments as the hook.

```php
use Dllobell\Hooks\Hooks;

$hooks = Hooks::create();

$hooks->before('user.created', function (array $user): void {
	// Runs before all 'user.created' handlers
});

$hooks->register('user.created', function (array $user): void {
	// ...
});

$hooks->call('user.created', ['id' => 7]);
```

### Per-hook after callbacks

Run logic after handlers for a specific hook. The callback receives the same arguments as the hook.

```php
use Dllobell\Hooks\Hooks;

$hooks = Hooks::create();

$hooks->after('invoice.paid', function (array $invoice): void {
	// Runs after all 'invoice.paid' handlers
});

$hooks->register('invoice.paid', function (array $invoice): void {
	// ...
});

$hooks->call('invoice.paid', ['id' => 99]);
```

### Redirecting hook names

Alias or migrate hook names by redirecting from one name to another. Registrations made under the redirected-from name will be invoked when calling the target name. Chained redirects are resolved. Calling the redirected-from name itself is a no-op.

```php
use Dllobell\Hooks\Hooks;

$hooks = Hooks::create();

// Redirect from legacy name to the new name
$hooks->redirect('legacy.user.created', 'user.created');

// Register under the legacy name
$hooks->register('legacy.user.created', function (array $user): void {
	// will be called when calling 'user.created'
});

// Call the target name -> legacy handler runs
$hooks->call('user.created', ['id' => 2]);

// Calling the redirected-from name does nothing
$hooks->call('legacy.user.created');
```

### Execution order

When `call('name', ...)` is invoked, callbacks run in this order:

1) All `beforeEach` callbacks (in registration order)
2) All `before('name', ...)` callbacks (in registration order)
3) All `register('name', ...)` handlers (in registration order)
4) All `after('name', ...)` callbacks (in registration order)
5) All `afterEach` callbacks (in registration order)

```php
use Dllobell\Hooks\Hooks;

$hooks = Hooks::create();

$order = [];

$hooks->beforeEach(function () use (&$order): void {
    $order[] = 'beforeEach';
});
$hooks->before('email.send', function () use (&$order): void {
    $order[] = 'before';
});
$hooks->register('email.send', function () use (&$order): void {
    $order[] = 'handler1';
});
$hooks->register('email.send', function () use (&$order): void {
    $order[] = 'handler2';
});
$hooks->after('email.send', function () use (&$order): void {
    $order[] = 'after';
});
$hooks->afterEach(function () use (&$order): void {
    $order[] = 'afterEach';
});

$hooks->call('email.send');

// $order === ['beforeEach', 'before', 'handler1', 'handler2', 'after', 'afterEach']
```

## Credits

This package is heavily inspired by the [hookable](https://github.com/unjs/hookable) Javascript package, I just wanted to make a simple PHP port for fun.

## License

The MIT License (MIT). See the [license file](LICENSE) for more information.
