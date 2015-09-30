<?php

namespace Cachedecorator;


use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Can we create a service by the requested name?
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @param  string $name
     * @param  string $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator);

        return (isset($config[$requestedName]) && class_exists($requestedName));
    }

    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        $config = $services->get('Config');

        if (!isset($config[\Cachedecorator\Module::CACHED])) {
            throw new \Exception('config not configured for cache decorator');
        }

        $this->config = $config[\Cachedecorator\Module::CACHED];

        return $this->config;
    }

        /**
     * Create a service
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @param  string $name
     * @param  string $requestedName
     * @return \Zend\Db\Adapter\Adapter
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $service = new $requestedName;

        if ($service instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
            $service->setServiceLocator($serviceLocator);
        }

        $config = $this->getConfig($serviceLocator);

        $decorator = new Decorator;
        $decorator->setCacheStorage($serviceLocator->get(\Cachedecorator\Module::CACHE));
        $decorator->setService($service);
        $decorator->setAllowedMethods($config[$requestedName]);

        return $decorator;
    }
}
