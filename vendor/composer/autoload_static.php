<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3846040d78c87bdde3e70c424823ee6c
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MessageBird\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MessageBird\\' =>
        array (
            0 => __DIR__ . '/..' . '/messagebird/php-rest-api/src/MessageBird',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3846040d78c87bdde3e70c424823ee6c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3846040d78c87bdde3e70c424823ee6c::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
