<?php

namespace ForestAdmin\SymfonyForestAdmin\Controller;


use ForestAdmin\AgentPHP\Agent\Http\ForestController as BaseForestController;
use ForestAdmin\AgentPHP\Agent\Routes\Response\SimpleJsonResponse;
use ForestAdmin\SymfonyForestAdmin\Service\ForestAgent;
use ForestAdmin\SymfonyForestAdmin\Transformer\EntityTransformer;

class ForestController extends BaseForestController
{
    public function __construct(protected ForestAgent $forestAgent)
    {
    }
}
