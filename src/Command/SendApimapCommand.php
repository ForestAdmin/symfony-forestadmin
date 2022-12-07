<?php

namespace ForestAdmin\SymfonyForestAdmin\Command;

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\Agent\ForestAdminHttpDriver;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ForestAdminHttpDriver::sendSchema(AgentFactory::get('datasource'));

        $output->writeln('<info>Apimap sent</info>');

        return Command::SUCCESS;
    }
}
