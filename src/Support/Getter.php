<?php

namespace Please\Container\Support;

class Getter
{
    public function __construct(protected array $data)
    {
        //
    }

    public function __invoke(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}