<?php

declare(strict_types=1);

namespace Dllobell\Hooks;

use Closure;

final class Hooks
{
    /**
     * @var array<string, array<Closure>>
     */
    private array $hooks = [];

    /**
     * @var array<string, string>
     */
    private array $redirections = [];

    /**
     * @var array<Closure>
     */
    private array $beforeEachCallbacks = [];

    /**
     * @var array<Closure>
     */
    private array $afterEachCallbacks = [];

    /**
     * @var array<string, array<Closure>>
     */
    private array $beforeCallbacks = [];

    /**
     * @var array<string, array<Closure>>
     */
    private array $afterCallbacks = [];

    private function __construct() {}

    public static function create(): self
    {
        return new self();
    }

    public function register(string $name, Closure $handler): Closure
    {
        $name = $this->resolveName($name);

        if (!array_key_exists($name, $this->hooks)) {
            $this->hooks[$name] = [];
        }

        $this->hooks[$name][] = $handler;

        return fn () => $this->unregister($name, $handler);
    }

    public function registerOnce(string $name, Closure $handler): Closure
    {
        $unregister = $this->register($name, function (...$arguments) use ($handler, &$unregister) {
            if ($unregister !== null) {
                $unregister();
            }

            return $handler(...$arguments);
        });

        return $unregister;
    }

    public function unregister(string $name, Closure $handler): void
    {
        $name = $this->resolveName($name);

        if (array_key_exists($name, $this->hooks)) {
            $this->hooks[$name] = array_filter(
                $this->hooks[$name],
                fn (Closure $other) => $other !== $handler,
            );
        }
    }

    public function redirect(string $from, string $to): void
    {
        $this->redirections[$from] = $to;
    }

    public function beforeEach(Closure $callback): void
    {
        $this->beforeEachCallbacks[] = $callback;
    }

    public function afterEach(Closure $callback): void
    {
        $this->afterEachCallbacks[] = $callback;
    }

    public function before(string $name, Closure $callback): void
    {
        $name = $this->resolveName($name);

        if (!array_key_exists($name, $this->beforeCallbacks)) {
            $this->beforeCallbacks[$name] = [];
        }

        $this->beforeCallbacks[$name][] = $callback;
    }

    public function after(string $name, Closure $callback): void
    {
        $name = $this->resolveName($name);

        if (!array_key_exists($name, $this->afterCallbacks)) {
            $this->afterCallbacks[$name] = [];
        }

        $this->afterCallbacks[$name][] = $callback;
    }

    public function call(string $name, mixed ...$arguments): void
    {
        foreach ($this->beforeEachCallbacks as $callback) {
            $callback($name, ...$arguments);
        }

        foreach ($this->beforeCallbacks[$name] ?? [] as $callback) {
            $callback(...$arguments);
        }

        if (array_key_exists($name, $this->hooks)) {
            foreach ($this->hooks[$name] as $hook) {
                $hook(...$arguments);
            }
        }

        foreach ($this->afterCallbacks[$name] ?? [] as $callback) {
            $callback(...$arguments);
        }

        foreach ($this->afterEachCallbacks as $callback) {
            $callback($name, ...$arguments);
        }
    }

    private function resolveName(string $name): string
    {
        while (array_key_exists($name, $this->redirections)) {
            $name = $this->redirections[$name];
        }

        return $name;
    }
}
