<?php

use ForestAdmin\SymfonyForestAdmin\Command\InstallCommand;
use ForestAdmin\SymfonyForestAdmin\Command\SendApimapCommand;
use ForestAdmin\SymfonyForestAdmin\EventListener\ForestCors;
use ForestAdmin\SymfonyForestAdmin\Service\ForestAgent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set('forest.agent', ForestAgent::class)
        ->public()
        ->arg('$entityManager', service('doctrine.orm.entity_manager'));

    $services->alias(ForestAgent::class, 'forest.agent');

    $services->load('ForestAdmin\\SymfonyForestAdmin\\Controller\\', '../src/Controller')
        ->tag('controller.service_arguments');

    $services->set('forest.cors', ForestCors::class)
        ->tag('kernel.event_subscriber');

    $services
        ->set(SendApimapCommand::class)
        ->public()
        ->arg('$forestAgent', service('forest.agent'))
        ->tag('console.command');

    $services
        ->set(InstallCommand::class)
        ->public()
        ->arg('$projectDir', '%kernel.project_dir%')
        ->tag('console.command');
};
