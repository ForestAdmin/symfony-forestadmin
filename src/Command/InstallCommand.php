<?php

namespace ForestAdmin\SymfonyForestAdmin\Command;

use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class InstallCommand extends Command
{
    public function __construct(private KernelInterface $appKernel)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('forest:install')
            ->addArgument('secretKey', InputArgument::REQUIRED, 'The secret key provided by Forest Admin')
            ->addArgument('envFileName', InputArgument::OPTIONAL, 'name of the env file', '.env')
            ->setDescription('Install the Forest admin : setup environment keys & publish the default Forest Admin configuration to the application');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->deleteDirectory($this->appKernel->getContainer()->getParameter('kernel.cache_dir') . '/forest');

        $keys = [
            'FOREST_AUTH_SECRET' => Str::random(32),
            'FOREST_ENV_SECRET'  => $input->getArgument('secretKey'),
        ];

        if (isset($_SERVER['FOREST_SERVER_URL'])) {
            $keys['FOREST_SERVER_URL'] = $_SERVER['FOREST_SERVER_URL'];
        }

        $this->addKeysToEnvFile($output, $keys, $input->getArgument('envFileName'));

        $this->publishConfig($output);

        return Command::SUCCESS;
    }

    private function addKeysToEnvFile(OutputInterface $output, array $keys, string $envFileName): void
    {
        foreach ($keys as $key => $value) {
            file_put_contents($this->appKernel->getProjectDir() . '/' . $envFileName, PHP_EOL . "$key=$value", FILE_APPEND);
        }
        $output->writeln('<info>✅ Env keys correctly set</info>');
    }

    private function publishConfig(OutputInterface $output)
    {
        $defaultConfigFile = __DIR__ . '/../../default.config';
        $publishFileName = $this->appKernel->getProjectDir() . '/forest/symfony_forest_admin.php';
        if (! file_exists($publishFileName)) {
            $forestDirectory = $this->appKernel->getProjectDir() . '/forest';
            if (! mkdir($forestDirectory) && ! is_dir($forestDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $forestDirectory));
            }
            copy($defaultConfigFile, $publishFileName);
            $output->writeln('<info>✅ Config file set</info>');
        } else {
            $output->writeln('<info>⚠️  Forest Admin config file already setup</info>');
        }
    }

    private function deleteDirectory($dirname)
    {
        // Sanity check
        if (! file_exists($dirname)) {
            return false;
        }

        // Simple delete for a file
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }

        // Loop through the folder
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Recurse
            $this->deleteDirectory($dirname . DIRECTORY_SEPARATOR . $entry);
        }

        // Clean up
        $dir->close();

        return rmdir($dirname);
    }
}
