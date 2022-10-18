<?php

use Nicolas\SymfonyForestAdmin\Controller\ForestController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes) {
    $routes->import('forest.agent::loadRoutes', 'service');
};
