<?php

namespace Nicolas\SymfonyForestAdmin\Service;

use Doctrine\ORM\EntityManagerInterface;
use ForestAdmin\AgentPHP\Agent\Builder\Agent;
use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\Agent\Http\Router;
use ForestAdmin\AgentPHP\DatasourceDoctrine\DoctrineDatasource;
use ForestAdmin\AgentPHP\DatasourceToolkit\Components\Charts\Chart;
use ForestAdmin\AgentPHP\DatasourceToolkit\Components\Contracts\DatasourceContract;
use Nicolas\SymfonyForestAdmin\Controller\ForestController;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ForestAgent implements RouteLoaderInterface
{
    public array $options;

    public AgentFactory $agent;

    /**
     * @throws \ReflectionException
     */
    public function __construct(private KernelInterface $appKernel, private EntityManagerInterface $entityManager)
    {
        $this->options = [
            'debug'           => true,
            'authSecret'      => 'RykWz6JrqD0ctwzIXDfXeb6J8CDZqHMy',
//            'agentUrl'        => 'https://localhost:8000',
            'agentUrl'        => 'https://production.development.forestadmin.com',
            'envSecret'       => 'dab924020263d05f608994e9f39ae47cbae3154426cbbbeb5c1906932b99fd02', // prod
//            'envSecret'       => '8ac1173b520cf9f91654a9b074d69d31ee2835491dbf5b78294e6f2138019eeb',
            'forestServerUrl' => 'https://api.development.forestadmin.com',
            'isProduction'    => false,
            'loggerLevel'     => 'Info',
            'prefix'          => 'forest',
            'schemaPath'      => $this->appKernel->getProjectDir() . '/.forestadmin-schema.json',
            'projectDir'      => $this->appKernel->getProjectDir()
        ];
        $this->agent = new AgentFactory($this->options, ['orm' => $this->entityManager]);
        $this->loadConfiguration();
    }

    public function loadConfiguration(): void
    {
        if (file_exists($this->appKernel->getProjectDir() . '/config/packages/symfony_forest_admin.php')) {
            $callback = require $this->appKernel->getProjectDir() . '/config/packages/symfony_forest_admin.php';
            $callback($this);
        } else {
            // set the default datasource for symfony app
            $this->agent->addDatasource(new DoctrineDatasource($this->entityManager));
        }
    }

    /**
     * @return RouteCollection
     */
    public function loadRoutes()
    {
        $routes = new RouteCollection();
        foreach ($this->getRoutes() as $routeName => $route) {
            $route = new Route(path:'forest' . $route['uri'], defaults: ['_controller' => ForestController::class], methods: $route['methods']);
            $routes->add($routeName, $route);
        }

        return $routes;
    }

    public function getRoutes(): array
    {
        return Router::getRoutes();
    }

    public function renderChart(Chart $chart): array
    {
        return $this->agent->renderChart($chart);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
