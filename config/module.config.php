<?php

return [
    'service_manager' => [
        'abstract_factories' => [
            \Cachedecorator\AbstractFactory::class,
            \Zend\Cache\Service\StorageCacheAbstractServiceFactory::class,
        ],
        'invokables' => [
            \Cachedecorator\Module::DECORATOR_CLASS => \Cachedecorator\Decorator::class,
        ],
    ],
];
