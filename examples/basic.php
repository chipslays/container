<?php

use Please\Container\Container;
use Please\Container\Support\Getter;

require __DIR__ . '/../vendor/autoload.php';

class Foo
{
    function __construct(?string $value = null)
    {
        dump('foo::created', $value);
    }
}

$container = new Container;

$container->bind(Foo::class);
dump($container->get(Foo::class));

$container->bind(Foo::class);
dump($container->get(Foo::class, ['value' => 'bar']));

$container->bind(Foo::class, function (Getter $get) {
    return new Foo($get('value'));
});
dump($container->get(Foo::class, ['value' => 'bar']));

$container->bind('foo', Foo::class);
dump($container->get('foo'));

$container->bind('foo', 'bar');
dump($container->get('foo'));

$container->bind('foo', ['foo', 'bar']);
dump($container->get('foo'));

$container->bind('foo', true === true);
dump($container->get('foo'));

$container->bind('foo', 1337);
dump($container->get('foo'));

$container->bind('foo', 13.37);
dump($container->get('foo'));

$container->bind('baz', null);
dump($container->get('baz'));

$container->bind('foo', new stdClass);
dump($container->get('foo'));

$container->bind('foo', fopen('php://stdout', 'r'));
dump($container->get('foo'));


