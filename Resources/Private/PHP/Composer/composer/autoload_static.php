<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita295233594042d2e2f9162ee47cd5320
{
    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'ParsedownExtra' => 
            array (
                0 => __DIR__ . '/..' . '/erusev/parsedown-extra',
            ),
            'Parsedown' => 
            array (
                0 => __DIR__ . '/..' . '/erusev/parsedown',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInita295233594042d2e2f9162ee47cd5320::$prefixesPsr0;
            $loader->classMap = ComposerStaticInita295233594042d2e2f9162ee47cd5320::$classMap;

        }, null, ClassLoader::class);
    }
}
