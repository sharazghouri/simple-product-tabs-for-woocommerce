<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5e0cbe299cdcfdacc1610980c5074e29
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Solution_Box\\Plugin\\Simple_Product_Tabs\\' => 40,
            'SolutionBoxSettings\\' => 20,
        ),
        'D' => 
        array (
            'Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\' => 55,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Solution_Box\\Plugin\\Simple_Product_Tabs\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'SolutionBoxSettings\\' => 
        array (
            0 => __DIR__ . '/..' . '/solutionbox/wordpress-settings-framework/src',
        ),
        'Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\' => 
        array (
            0 => __DIR__ . '/..' . '/dealerdirect/phpcodesniffer-composer-installer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5e0cbe299cdcfdacc1610980c5074e29::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5e0cbe299cdcfdacc1610980c5074e29::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5e0cbe299cdcfdacc1610980c5074e29::$classMap;

        }, null, ClassLoader::class);
    }
}
