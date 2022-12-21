<?php

namespace ForestAdmin\SymfonyForestAdmin\Command;

use ForestAdmin\SymfonyForestAdmin\Service\ForestAgent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendApimapCommand extends Command
{
    public function __construct(protected ForestAgent $forestAgent)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('forest:send-apimap')
            ->setDescription('Send the apimap to Forest');
    }

    /**
     * @throws \ErrorException
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->forestAgent->agent->sendSchema();

        $output->writeln('<info>Apimap sent</info>');

        return Command::SUCCESS;
    }
}
