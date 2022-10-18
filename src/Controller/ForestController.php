<?php

namespace Nicolas\SymfonyForestAdmin\Controller;

use Doctrine\Persistence\ManagerRegistry;
use ForestAdmin\AgentPHP\Agent\Facades\JsonApi;
use ForestAdmin\AgentPHP\Agent\Http\ForestController as BaseForestController;
use ForestAdmin\AgentPHP\Agent\Routes\Response\SimpleJsonResponse;
use Nicolas\SymfonyForestAdmin\Service\ForestAgent;
use Nicolas\SymfonyForestAdmin\Transformer\EntityTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForestController extends BaseForestController
{
    public function __construct(protected ForestAgent $forestAgent)
    {
    }
}
