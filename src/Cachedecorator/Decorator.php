<?php

namespace Cachedecorator;

class Decorator
{
    protected $service;

    protected $allowedMethods;

    /** @var  string */
    protected $serviceName;

    protected $cacheStorage;

    public function setService($service)
    {
        $this->service = $service;
        $this->serviceName = get_class($service);

        return $this;
    }

    public function setCacheStorage($cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;

        return $this;
    }

    public function setAllowedMethods(array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;

        return $this;
    }

    public function __call($method, $args)
    {
        $useCache = in_array($method, $this->allowedMethods);

        if($useCache) {
            $key = $this->serviceName . '.' . $method . '.' . md5(serialize($args));

            $success = null;
            $data = $this->cacheStorage->getItem($key, $success);

            if ($success) {
                return $data;
            }
        }

        $data = call_user_func_array([$this->service, $method], $args);

        if($useCache) {
            if ($data instanceof \Iterator) {
                $data = iterator_to_array($data);
            }

            $this->cacheStorage->setItem($key, $data);
        }

        return $data;
    }
}
