<?php

use Please\Container\Container as BaseContainer;
use Please\Container\Support\Traits\Singleton;

require __DIR__ . '/../vendor/autoload.php';

class Container extends BaseContainer
{
    use Singleton;
}

class Foo
{
    function __construct(public Bar $bar)
    {
        dump('foo::created');
    }
}

class Bar
{
    function __construct(public Baz $baz)
    {
        dump('bar::created');
    }
}

class Baz
{
    function __construct()
    {
        dump('baz::created');
    }
}

$container = Container::getInstance();

$container->bind(Foo::class);
$container->bind('bar', Bar::class);
$container->bind([Baz::class, 'baz', 'baz-alias'], Baz::class);

$container->get(Foo::class);

echo PHP_EOL;

$container->get(Bar::class);

echo PHP_EOL;

$container->get('baz-alias');
