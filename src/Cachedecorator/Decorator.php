<?php

namespace Cachedecorator;

class Decorator
{
    /** @var mixed Cacheble class */
    protected $service;

    /** @var array This methods will be cached */
    protected $allowedMethods = [];

    /** @var string Name of the class */
    protected $serviceName;

    /** @var \Zend\Cache\Storage\StorageInterface Cache storage */
    protected $cacheStorage;

    /**
     * Set cacheble service
     *
     * @param $service mixed
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;
        $this->serviceName = get_class($service);

        return $this;
    }

    /**
     * @param $cacheStorage \Zend\Cache\Storage\StorageInterface
     * @return $this
     */
    public function setCacheStorage($cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;

        return $this;
    }

    /**
     * Only this methods will be cached
     *
     * @param array $allowedMethods
     * @return $this
     */
    public function setAllowedMethods(array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;

        return $this;
    }

    /**
     * Call method of cacheble service class
     *
     * @param string $method
     * @param array $args
     * @return array|mixed
     */
    public function __call($method, $args)
    {
        $useCache = in_array($method, $this->allowedMethods);

        if ($useCache) {
            $key = $this->serviceName . '.' . $method . '.' . md5(serialize($args));

            $success = null;
            $data = $this->cacheStorage->getItem($key, $success);

            if ($success) {
                return $data;
            }
        }

        $data = call_user_func_array([$this->service, $method], $args);

        if ($useCache) {
            if ($data instanceof \Iterator) {
                $data = iterator_to_array($data);
            }

            $this->cacheStorage->setItem($key, $data);
        }

        return $data;
    }
}
