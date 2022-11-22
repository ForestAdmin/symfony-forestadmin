<?php

namespace ForestAdmin\SymfonyForestAdmin\Service;

use Doctrine\ORM\EntityManagerInterface;
use ForestAdmin\AgentPHP\Agent\Builder\Agent;
use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\Agent\Http\Router;
use ForestAdmin\AgentPHP\Agent\Utils\Env;
use ForestAdmin\AgentPHP\DatasourceDoctrine\DoctrineDatasource;
use ForestAdmin\AgentPHP\DatasourceToolkit\Components\Charts\Chart;
use ForestAdmin\SymfonyForestAdmin\Controller\ForestController;
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
        $this->options = $this->loadOptions();
        $this->agent = new AgentFactory($this->options);
        $this->loadConfiguration();
    }

    private function loadConfiguration(): void
    {
        if (file_exists($this->appKernel->getProjectDir() . '/config/packages/symfony_forest_admin.php')) {
            $callback = require $this->appKernel->getProjectDir() . '/config/packages/symfony_forest_admin.php';
            $callback($this);
        } else {
            // set the default datasource for symfony app
            $this->agent->addDatasource(new DoctrineDatasource($this->entityManager));
        }
    }

    private function loadOptions(): array
    {
        return [
            'debug'            => Env::get('FOREST_DEBUG', true),
            'authSecret'       => Env::get('FOREST_AUTH_SECRET'),
            'agentUrl'         => Env::get('FOREST_AGENT_URL'),
            'envSecret'        => Env::get('FOREST_ENV_SECRET'),
            'forestServerUrl'  => Env::get('FOREST_SERVER_URL', 'https://api.forestadmin.com'),
            'isProduction'     => Env::get('FOREST_IS_PRODUCTION', false),
            'loggerLevel'      => Env::get('FOREST_LOGGER_LEVEL', 'Info'),
            'prefix'           => Env::get('FOREST_PREFIX', 'forest'),
            'schemaPath'       => $this->appKernel->getProjectDir() . '/.forestadmin-schema.json',
            'projectDir'       => $this->appKernel->getProjectDir(),
            'databaseDriver'   => Env::get('DATABASE_DRIVER'),
            'databaseHost'     => Env::get('DATABASE_HOST'),
            'databasePort'     => Env::get('DATABASE_PORT'),
            'databaseName'     => Env::get('DATABASE_NAME'),
            'databaseUsername' => Env::get('DATABASE_USERNAME'),
            'databasePassword' => Env::get('DATABASE_PASSWORD'),
        ];
    }

    /**
     * @return RouteCollection
     */
    public function loadRoutes()
    {
        $routes = new RouteCollection();
        foreach ($this->getRoutes() as $routeName => $route) {
            $route = new Route(path: 'forest' . $route['uri'], defaults: ['_controller' => ForestController::class], methods: $route['methods']);
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
