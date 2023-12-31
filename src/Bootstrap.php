<?php

namespace Chipslays\App;

class Bootstrap
{
    public static function autoload($dirs)
    {
        foreach ($dirs as $dir) {
            foreach (glob($dir) as $key => $file) {
                require_once $file;
            }
        }
    }
}
