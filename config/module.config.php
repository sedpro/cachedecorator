<?php

return [
    'service_manager' => [
        'abstract_factories' => [
            'Cachedecorator\AbstractFactory',
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
        ],
    ],
];
