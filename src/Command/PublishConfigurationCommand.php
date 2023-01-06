<?php

namespace ForestAdmin\SymfonyForestAdmin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishConfigurationCommand extends Command
{
    public function __construct(private string $projectDir)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('forest:publish-configuration')
            ->setDescription('Publish the default Forest Admin configuration to the application');
    }

    /**
     * @throws \ErrorException
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $defaultConfigFile = __DIR__ . '/../../config/default.config';
        $publishFileName = $this->projectDir . '/config/packages/symfony_forest_admin.php';
        if (! file_exists($publishFileName)) {
            copy($defaultConfigFile, $publishFileName);
            $output->writeln('<info>✅ Config file set</info>');
        } else {
            $output->writeln('<info>⚠️  Forest Admin config file already setup</info>');
        }

        return Command::SUCCESS;
    }
}
