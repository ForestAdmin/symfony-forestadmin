<?php

namespace ForestAdmin\SymfonyForestAdmin\Service;

use Doctrine\ORM\EntityManagerInterface;
use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\Agent\Http\Router;
use ForestAdmin\AgentPHP\Agent\Utils\Env;
use ForestAdmin\AgentPHP\DatasourceToolkit\Components\Charts\Chart;
use ForestAdmin\AgentPHP\DatasourceToolkit\Components\Contracts\DatasourceContract;
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
     * @param KernelInterface        $appKernel
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(private KernelInterface $appKernel, private EntityManagerInterface $entityManager)
    {
        $this->options = $this->loadOptions();
        $this->agent = new AgentFactory($this->options);
        $this->loadConfiguration();
    }

    public function addDatasource(DatasourceContract $datasource, array $options = []): self
    {
        $this->agent->addDatasource($datasource, $options);

        return $this;
    }

    public function addChart(string $name, \Closure $definition): self
    {
        $this->agent->addChart($name, $definition);

        return $this;
    }

    public function use(string $plugin, array $options = []): self
    {
        $this->agent->use($plugin, $options);
    }

    public function build(): void
    {
        $this->agent->build();
    }

    public function customizeCollection(string $name, \Closure $handle): self
    {
        $this->agent->customizeCollection($name, $handle);

        return $this;
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

    private function loadConfiguration(): void
    {
        if (file_exists($this->appKernel->getProjectDir() . '/forest/symfony_forest_admin.php')) {
            $callback = require $this->appKernel->getProjectDir() . '/forest/symfony_forest_admin.php';
            $callback($this);
        }
    }

    private function loadOptions(): array
    {
        return [
            'debug'                => Env::get('FOREST_DEBUG', true),
            'authSecret'           => Env::get('FOREST_AUTH_SECRET'),
            'envSecret'            => Env::get('FOREST_ENV_SECRET'),
            'forestServerUrl'      => Env::get('FOREST_SERVER_URL', 'https://api.forestadmin.com'),
            'isProduction'         => Env::get('APP_ENV', 'dev') === 'prod',
            'prefix'               => Env::get('FOREST_PREFIX', 'forest'),
            'permissionExpiration' => Env::get('FOREST_PERMISSIONS_EXPIRATION_IN_SECONDS', 1),
            'cacheDir'             => $this->appKernel->getContainer()->getParameter('kernel.cache_dir') . '/forest',
            'schemaPath'           => $this->appKernel->getProjectDir() . '/.forestadmin-schema.json',
            'projectDir'           => $this->appKernel->getProjectDir(),
        ];
    }
}
