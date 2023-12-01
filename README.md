# 📦 DI Container

Yet another dependency injection container implementation with PSR-11 compliant for PHP >8.0.

## Installation

```bash
composer require please/container
```

## Basic usage
```php
use Please\Container\Container;

$container = new Container;

$container->bind(Mailer::class, function (Container $container, array $parameters) {
    return new Mailer::($parameters['user'], $parameters['password']);
});

/** @var Mailer */
$foo = $container->get(Mailer::class, [
  'user' => 'admin',
  'password' => 'qwerty',
]);
```

```php
use Please\Container\Container;

$container = new Container;

$container->singleton('timestamp', fn () => time());

echo $container->get('timestamp'); // always returns the same value
```

## Examples

You can find usage examples [here](/examples).

## Documentation

You can bind any abstract aliases.

```php
use Please\Container\Container;

$container = new Container;

$container->bind(Foo::class);
$container->get(Foo::class); // ok
$container->get('foo'); // error

// if you pass a class string
// it is also automatically bind as `Foo::class`
$container->bind('foo', Foo::class);
$container->get(Foo::class); // ok
$container->get('foo'); // ok

// array of aliases
$container->bind([Foo::class, 'foo', 'another-foo-alias'], Foo::class);
$container->get(Foo::class); // ok
$container->get('foo'); // ok
$container->get('another-foo-alias'); // ok
```

You can bind any primitive data types.

```php
$container->bind('foo', 'bar');
$container->bind('foo', 1337);
$container->bind('foo', 13.37);
$container->bind('foo', true || false);
$container->bind('foo', ['bar', 'baz']);
$container->bind('foo', new stdClass);
$container->bind('foo', fopen('php://stdout', 'r'));

$container->bind('baz', null);
$container->get('baz') // returns `baz`
```

Generates a singleton instance of a class.

```php
// always returns the same value
$container->singleton('timestamp', fn () => time());
$container->bind('timestamp', fn () => time(), true);
```

Check if the container has a binding or instance for the given abstract.

```php
$container->bind('foo', 'bar');
$container->has('foo'); // true
$container->has('baz'); // false
```

## Singleton Pattern

If you really need to, you can use the container as a singleton.

```php
use Please\Container\Container as BaseContainer;
use Please\Container\Support\Traits\Singleton;

class Container extends BaseContainer
{
    use Singleton;
}

$container = Container::getInstance();
```

You can also check singleton example [here](/examples/singleton.php).

## License
Open-sourced software licensed under the [MIT license](https://opensource.org/license/mit/).