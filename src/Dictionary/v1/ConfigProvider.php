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
            'invokables' => [
                Action\ApresyanDictionaryAction::class => Action\ApresyanDictionaryAction::class,
            ],
            'factories' => [
                Action\OfflineDictionaryAction::class => Factory\OfflineDictionaryFactory::class,
            ],
        ];
    }
}
