<?php

declare(strict_types=1);

namespace Dllobell\Hooks\Tests;

use Dllobell\Hooks\Hooks;

describe('Hooks', function (): void {
    it('should register a hook handler', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $hooks->register('test', function () use (&$called): void {
            $called = true;
        });

        expect($called)->toBe(false);
    });

    it('should call a registered hook handler', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $hooks->register('test', function () use (&$called): void {
            $called = true;
        });

        $hooks->call('test');

        expect($called)->toBe(true);
    });

    it('should call hook handlers in registration order', function (): void {
        $hooks = Hooks::create();

        $calls = [];

        $hooks->register('test', function () use (&$calls): void {
            $calls[] = '1';
        });
        $hooks->register('test', function () use (&$calls): void {
            $calls[] = '2';
        });

        $hooks->call('test');

        expect($calls)->toBe(['1', '2']);
    });

    it('should not call an unregistered hook handler', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $unregister = $hooks->register('test', function () use (&$called): void {
            $called = true;
        });

        $unregister();

        $handler = function () use (&$called): void {
            $called = true;
        };

        $hooks->register('test', $handler);

        $hooks->unregister('test', $handler);

        $hooks->call('test');

        expect($called)->toBe(false);
    });

    it('should call a once hook handler only once', function (): void {
        $hooks = Hooks::create();

        $count = 0;

        $hooks->registerOnce('test', function () use (&$count): void {
            $count++;
        });

        $hooks->call('test');
        $hooks->call('test');

        expect($count)->toBe(1);
    });

    it('should pass arguments to handlers', function (): void {
        $hooks = Hooks::create();

        $seen = null;

        $hooks->register('test', function (...$args) use (&$seen): void {
            $seen = $args;
        });

        $hooks->call('test', 1, 'a', ['x' => 1]);

        expect($seen)->toBe([1, 'a', ['x' => 1]]);
    });

    it('should call beforeEach callback', function (): void {
        $hooks = Hooks::create();

        $hook = null;

        $hooks->beforeEach(function (string $name) use (&$hook): void {
            $hook = $name;
        });

        $hooks->call('test');

        expect($hook)->toBe('test');
    });

    it('should call beforeEach callback before the handlers', function (): void {
        $hooks = Hooks::create();

        $calls = [];

        $hooks->beforeEach(function () use (&$calls): void {
            $calls[] = 'beforeEach';
        });

        $hooks->register('test', function () use (&$calls): void {
            $calls[] = 'handler';
        });

        $hooks->call('test');

        expect($calls)->toBe(['beforeEach', 'handler']);
    });

    it('should call beforeEach callbacks in registration order', function (): void {
        $hooks = Hooks::create();

        $order = [];

        $hooks->beforeEach(function () use (&$order): void {
            $order[] = 'beforeEach1';
        });

        $hooks->beforeEach(function () use (&$order): void {
            $order[] = 'beforeEach2';
        });

        $hooks->register('test', function () use (&$order): void {
            $order[] = 'handler';
        });

        $hooks->call('test');

        expect($order)->toBe(['beforeEach1', 'beforeEach2', 'handler']);
    });

    it('should call beforeEach callback only once', function (): void {
        $hooks = Hooks::create();

        $count = 0;

        $hooks->beforeEach(function () use (&$count): void {
            $count++;
        });

        $hooks->register('test', function (): void {});
        $hooks->register('test', function (): void {});

        $hooks->call('test');

        expect($count)->toBe(1);
    });

    it('should pass the hook name and arguments to beforeEach callback', function (): void {
        $hooks = Hooks::create();

        $seenName = null;
        $seenArgs = null;

        $hooks->beforeEach(function (string $name, ...$args) use (&$seenName, &$seenArgs): void {
            $seenName = $name;
            $seenArgs = $args;
        });

        $hooks->call('test', 42, 'foo');

        expect($seenName)->toBe('test');
        expect($seenArgs)->toBe([42, 'foo']);
    });

    it('should call afterEach callback', function (): void {
        $hooks = Hooks::create();

        $hook = null;

        $hooks->afterEach(function (string $name) use (&$hook): void {
            $hook = $name;
        });

        $hooks->call('test');

        expect($hook)->toBe('test');
    });

    it('should call afterEach callback after the handlers', function (): void {
        $hooks = Hooks::create();

        $calls = [];

        $hooks->afterEach(function () use (&$calls): void {
            $calls[] = 'afterEach';
        });

        $hooks->register('test', function () use (&$calls): void {
            $calls[] = 'handler';
        });

        $hooks->call('test');

        expect($calls)->toBe(['handler', 'afterEach']);
    });

    it('should call afterEach callbacks in registration order', function (): void {
        $hooks = Hooks::create();

        $order = [];

        $hooks->afterEach(function () use (&$order): void {
            $order[] = 'afterEach1';
        });

        $hooks->afterEach(function () use (&$order): void {
            $order[] = 'afterEach2';
        });

        $hooks->register('test', function () use (&$order): void {
            $order[] = 'handler';
        });

        $hooks->call('test');

        expect($order)->toBe(['handler', 'afterEach1', 'afterEach2']);
    });

    it('should call afterEach callback only once', function (): void {
        $hooks = Hooks::create();

        $count = 0;

        $hooks->afterEach(function () use (&$count): void {
            $count++;
        });

        $hooks->register('test', function (): void {});
        $hooks->register('test', function (): void {});

        $hooks->call('test');

        expect($count)->toBe(1);
    });

    it('should pass the hook name and arguments to afterEach callback', function (): void {
        $hooks = Hooks::create();

        $seenName = null;
        $seenArgs = null;

        $hooks->afterEach(function (string $name, ...$args) use (&$seenName, &$seenArgs): void {
            $seenName = $name;
            $seenArgs = $args;
        });

        $hooks->call('test', 42, 'foo');

        expect($seenName)->toBe('test');
        expect($seenArgs)->toBe([42, 'foo']);
    });

    it('should call before callback if hook matches', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $hooks->before('test', function () use (&$called): void {
            $called = true;
        });

        $hooks->call('test');

        expect($called)->toBe(true);
    });

    it('should not call before callback if hook does not match', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $hooks->before('test', function () use (&$called): void {
            $called = true;
        });

        $hooks->call('missing');

        expect($called)->toBe(false);
    });

    it('should call before callback before the handlers', function (): void {
        $hooks = Hooks::create();

        $calls = [];

        $hooks->before('test', function () use (&$calls): void {
            $calls[] = 'before';
        });

        $hooks->register('test', function () use (&$calls): void {
            $calls[] = 'handler';
        });

        $hooks->call('test');

        expect($calls)->toBe(['before', 'handler']);
    });

    it('should call before callbacks in registration order', function (): void {
        $hooks = Hooks::create();

        $order = [];

        $hooks->before('test', function () use (&$order): void {
            $order[] = 'before1';
        });

        $hooks->before('test', function () use (&$order): void {
            $order[] = 'before2';
        });

        $hooks->register('test', function () use (&$order): void {
            $order[] = 'handler';
        });

        $hooks->call('test');

        expect($order)->toBe(['before1', 'before2', 'handler']);
    });

    it('should call before callback only once', function (): void {
        $hooks = Hooks::create();

        $count = 0;

        $hooks->before('test', function () use (&$count): void {
            $count++;
        });

        $hooks->register('test', function (): void {});
        $hooks->register('test', function (): void {});

        $hooks->call('test');

        expect($count)->toBe(1);
    });

    it('should pass the arguments to before callback', function (): void {
        $hooks = Hooks::create();

        $seen = null;

        $hooks->before('test', function (...$args) use (&$seen): void {
            $seen = $args;
        });

        $hooks->call('test', 42, 'foo');

        expect($seen)->toBe([42, 'foo']);
    });

    it('should call after callback if hook matches', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $hooks->after('test', function () use (&$called): void {
            $called = true;
        });

        $hooks->call('test');

        expect($called)->toBe(true);
    });

    it('should not call after callback if hook does not match', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $hooks->after('test', function () use (&$called): void {
            $called = true;
        });

        $hooks->call('missing');

        expect($called)->toBe(false);
    });

    it('should call after callback after the handlers', function (): void {
        $hooks = Hooks::create();

        $calls = [];

        $hooks->after('test', function () use (&$calls): void {
            $calls[] = 'after';
        });

        $hooks->register('test', function () use (&$calls): void {
            $calls[] = 'handler';
        });

        $hooks->call('test');

        expect($calls)->toBe(['handler', 'after']);
    });

    it('should call after callbacks in registration order', function (): void {
        $hooks = Hooks::create();

        $order = [];

        $hooks->after('test', function () use (&$order): void {
            $order[] = 'after1';
        });

        $hooks->after('test', function () use (&$order): void {
            $order[] = 'after2';
        });

        $hooks->register('test', function () use (&$order): void {
            $order[] = 'handler';
        });

        $hooks->call('test');

        expect($order)->toBe(['handler', 'after1', 'after2']);
    });

    it('should call after callback only once', function (): void {
        $hooks = Hooks::create();

        $count = 0;

        $hooks->after('test', function () use (&$count): void {
            $count++;
        });

        $hooks->register('test', function (): void {});
        $hooks->register('test', function (): void {});

        $hooks->call('test');

        expect($count)->toBe(1);
    });

    it('should pass the arguments to after callback', function (): void {
        $hooks = Hooks::create();

        $seen = null;

        $hooks->after('test', function (...$args) use (&$seen): void {
            $seen = $args;
        });

        $hooks->call('test', 42, 'foo');

        expect($seen)->toBe([42, 'foo']);
    });

    it('should call all callbacks in the correct order', function (): void {
        $hooks = Hooks::create();

        $order = [];

        $hooks->beforeEach(function () use (&$order): void {
            $order[] = 'beforeEach';
        });
        $hooks->before('test', function () use (&$order): void {
            $order[] = 'before';
        });
        $hooks->register('test', function () use (&$order): void {
            $order[] = 'handler1';
        });
        $hooks->register('test', function () use (&$order): void {
            $order[] = 'handler2';
        });
        $hooks->after('test', function () use (&$order): void {
            $order[] = 'after';
        });
        $hooks->afterEach(function () use (&$order): void {
            $order[] = 'afterEach';
        });

        $hooks->call('test');

        expect($order)->toBe([
            'beforeEach',
            'before',
            'handler1',
            'handler2',
            'after',
            'afterEach',
        ]);
    });

    it('can redirect registrations to another hook name', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $hooks->redirect('from', 'to');

        $hooks->register('from', function () use (&$called): void {
            $called = true;
        });

        $hooks->call('to');

        expect($called)->toBe(true);
    });

    it('should resolve chained redirections', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $hooks->redirect('a', 'b');
        $hooks->redirect('b', 'c');

        $hooks->register('a', function () use (&$called): void {
            $called = true;
        });

        $hooks->call('c');

        expect($called)->toBe(true);
    });

    it('should not call the hook handler if it was redirected', function (): void {
        $hooks = Hooks::create();

        $called = false;

        $hooks->redirect('from', 'to');

        $hooks->register('from', function () use (&$called): void {
            $called = true;
        });

        $hooks->call('from');

        expect($called)->toBe(false);
    });
});
