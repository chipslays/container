<?php

declare(strict_types=1);

namespace Please\Container\Support\Traits;

use Please\Container\Container;
use Please\Container\Exceptions\ContainerException;

trait Singleton
{
    /**
     * The single instance of the Container.
     *
     * @var Container|null
     */
    protected static ?Container $instance = null;

    /**
     * Get the singleton instance of the Container.
     *
     * @return Container
     */
    public static function getInstance(): Container
    {
        if (static::canInstantiate() && self::$instance === null) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * Prevent direct instantiation of the class.
     */
    protected function __construct()
    {
        //
    }

    /**
     * Prevent cloning of the singleton instance.
     */
    protected function __clone()
    {
        //
    }

    /**
     * Prevent unserializing of the singleton instance.
     *
     * @throws ContainerException
     */
    public function __wakeup()
    {
        throw new ContainerException('Cannot unserialize a container.');
    }

    /**
     * Check if the class can be instantiated.
     *
     * @return bool
     */
    protected static function canInstantiate(): bool
    {
        $reflection = new \ReflectionClass(static::class);
        return !$reflection->isAbstract();
    }
}
