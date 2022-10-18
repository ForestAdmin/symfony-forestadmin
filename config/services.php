<?php

use Nicolas\SymfonyForestAdmin\EventListener\ForestCors;
use Nicolas\SymfonyForestAdmin\Routing\RoutesLoader;
use Nicolas\SymfonyForestAdmin\Service\ForestAgent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function(ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set('forest.agent', ForestAgent::class)
        ->public()
        ->arg('$entityManager', service('doctrine.orm.entity_manager'));

    $services->alias(ForestAgent::class, 'forest.agent');

    $services->load('Nicolas\\SymfonyForestAdmin\\Controller\\', '../src/Controller')
        ->tag('controller.service_arguments');

    $services->set('forest.cors', ForestCors::class)
        ->tag('kernel.event_subscriber');
};
