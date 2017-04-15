<?php

namespace Dictionary\Factory;

use Common\Container\ConfigInterface;
use Dictionary\Action\OfflineDictionaryAction;
use Psr\Container\ContainerInterface;

class OfflineDictionaryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        return new OfflineDictionaryAction($config);
    }
}
