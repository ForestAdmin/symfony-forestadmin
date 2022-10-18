<?php

namespace Nicolas\SymfonyForestAdmin;

use Nicolas\SymfonyForestAdmin\DependencyInjection\SymfonyForestAdminExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class SymfonyForestAdminBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): SymfonyForestAdminExtension
    {
        return new SymfonyForestAdminExtension();
    }
}
