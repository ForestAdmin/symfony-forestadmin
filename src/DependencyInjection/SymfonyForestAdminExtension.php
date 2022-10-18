<?php

namespace Nicolas\SymfonyForestAdmin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SymfonyForestAdminExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loaderPHP = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );
        $loaderPHP->load('services.php');
    }
}
