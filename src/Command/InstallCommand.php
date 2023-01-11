<?php

namespace ForestAdmin\SymfonyForestAdmin\Command;

use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    public function __construct(private string $projectDir)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('forest:install')
            ->addArgument('secretKey', InputArgument::REQUIRED, 'The secret key provided by Forest Admin')
            ->addArgument('url', InputArgument::REQUIRED, 'The url agent')
            ->addArgument('envFileName', InputArgument::OPTIONAL, 'name of the env file', '.env')
            ->setDescription('Install the Forest admin : setup environment keys & publish the default Forest Admin configuration to the application');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keys = [
            'FOREST_AUTH_SECRET' => Str::random(32),
            'FOREST_AGENT_URL'   => $input->getArgument('url'),
            'FOREST_ENV_SECRET'  => $input->getArgument('secretKey'),
        ];
        $this->addKeysToEnvFile($output, $keys, $input->getArgument('envFileName'));

        $this->publishConfig($output);

        return Command::SUCCESS;
    }

    private function addKeysToEnvFile(OutputInterface $output, array $keys, string $envFileName): void
    {
        foreach ($keys as $key => $value) {
            file_put_contents($this->projectDir . '/' . $envFileName, PHP_EOL . "$key=$value", FILE_APPEND);
        }
        $output->writeln('<info>✅ Env keys correctly set</info>');
    }

    private function publishConfig(OutputInterface $output)
    {
        $defaultConfigFile = __DIR__ . '/../../config/default.config';
        $publishFileName = $this->projectDir . '/config/packages/symfony_forest_admin.php';
        if (! file_exists($publishFileName)) {
            copy($defaultConfigFile, $publishFileName);
            $output->writeln('<info>✅ Config file set</info>');
        } else {
            $output->writeln('<info>⚠️  Forest Admin config file already setup</info>');
        }
    }
}
