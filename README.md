CacheDecorator
============================
Version 0.1

This module allows you simple caching of your service methods.

Installation
------------
For the installation uses composer [composer](http://getcomposer.org "composer - package manager").

```sh
php composer.phar require  sedpro/cachedecorator:dev-master
```

Add this project in your composer.json:

  "require": {
    "sedpro/cachedecorator": "dev-master"
  }
  
Post Installation
------------
Configuration:
- Add the module of `config/application.config.php` under the array `modules`, insert `Cachedecorator`
- Remove services, you want to cache, from getServiceConfig function in file `Module.php`
- In your `config/autoload/global.php` file add two values:

  ```php
    'caches' => [
        \Cachedecorator\Module::STORAGE => [
            'adapter' => [
                'name' => 'memcached',
            ],
            'options' => [
                'ttl' => 3600,
                'servers' => [
                    'node0' => [
                        'host' => '127.0.0.1',
                        'port' => 11211,
                    ],
                ],
                'namespace' => 'some_ns:',
            ],
        ],
    ],
    \Cachedecorator\Module::METHODS => [
        'Application\Service\Example' => [
            'getItems',
        ],
    ],
    ```
  
'caches' contains all caches you use in project. They will be instantiate in abstact factory `Zend\Cache\Service\StorageCacheAbstractServiceFactory` which is called in `vendor/sedpro/cachedecorator/config/module.config.php`. If you are already using this factory, there will be no conflict. 

'\Cachedecorator\Module::STORAGE' is cache storage adapter, used to store the output of your services.
  
'\Cachedecorator\Module::METHODS' is list of services you want to cache. Cached will be only listed functions. 

Example
=====================================
If you use the configuration, showed above, method getItems of class Application\Service\Example will be cached. You can use it as usual: 

  ```php
    $exampleService = $this->getServiceLocator()->get('Application\Service\Example');
    $items = $exampleService->getItems(); // cached
    $values = $exampleService->getValues(); // not cached
  ```

