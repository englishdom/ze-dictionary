<?php

namespace Dictionary;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'factories' => [
                Action\OfflineDictionaryAction::class => Factory\OfflineDictionaryFactory::class,
                Action\ApresyanDictionaryAction::class => Factory\ApresyanDictionaryFactory::class,
            ],
        ];
    }
}
