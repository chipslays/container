<?php

namespace Please\Container\Support\Traits;

use Please\Container\Container;
use Please\Container\Exceptions\ContainerException;

trait Singleton
{
    private static ?Container $instance = null;

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    protected function __construct() {
        //
    }

    protected function __clone() {
        //
    }

    public function __wakeup()
    {
        throw new ContainerException('Cannot unserialize a container.');
    }
}
