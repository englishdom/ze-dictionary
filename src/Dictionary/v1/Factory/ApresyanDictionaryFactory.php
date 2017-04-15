<?php

namespace Dictionary\Factory;

use Common\Container\ConfigInterface;
use Dictionary\Action\ApresyanDictionaryAction;
use Psr\Container\ContainerInterface;

class ApresyanDictionaryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        return new ApresyanDictionaryAction($config);
    }
}
