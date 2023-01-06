<?php

namespace ForestAdmin\SymfonyForestAdmin\Command;

use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    public function __construct(private string $projectDir)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('forest:setup-keys')
            ->addArgument('secretKey', InputArgument::REQUIRED, 'The secret key provided by Forest Admin')
            ->addArgument('url', InputArgument::REQUIRED, 'The url agent')
            ->addArgument('envFileName', InputArgument::OPTIONAL, 'name of the env file', '.env')
            ->setDescription('Setup de the env keys');
    }

    /**
     * @throws \ErrorException
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keys = [
            'FOREST_AUTH_SECRET' => Str::random(32),
            'FOREST_AGENT_URL'   => $input->getArgument('url'),
            'FOREST_ENV_SECRET'  => $input->getArgument('secretKey'),
        ];
        $this->addKeysToEnvFile($keys, $input->getArgument('envFileName'));

        $output->writeln('<info>✅ Env keys correctly set</info>');

        return Command::SUCCESS;
    }

    private function addKeysToEnvFile($keys, $envFileName): void
    {
        foreach ($keys as $key => $value) {
            file_put_contents($this->projectDir . '/' . $envFileName, PHP_EOL . "$key=$value", FILE_APPEND);
        }
    }
}
