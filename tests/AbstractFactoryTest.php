<?php
namespace Cachedecorator\Tests;

/**
 * @covers Cachedecorator\AbstractFactory
 */
class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Cachedecorator\AbstractFactory
     */
    private $factory;

    /**
     * @dataProvider providerCanCreateServiceWithName
     */
    public function testCanCreateServiceWithName($config, $serviceClass, $expected)
    {
        $serviceLocator = $this->getServiceLocator($config);

        $result = $this->factory->canCreateServiceWithName($serviceLocator, null, $serviceClass);
        $this->assertEquals($result, $expected);
    }

    /**
     * Provider for testCreateServiceWithName
     * @return array
     */
    public function providerCanCreateServiceWithName()
    {
        $service = $this->getMock('stdClass', ['cached_method']);

        return [
            // can create service when config is ok & service to cache exists
            [
                [
                    \Cachedecorator\Module::METHODS => [
                        get_class($service) => ['cached_method'],
                    ]
                ],
                get_class($service),
                true,
            ],
            // service may have no methods to cache
            [
                [
                    \Cachedecorator\Module::METHODS => [
                        get_class($service) => [],
                    ]
                ],
                get_class($service),
                true,
            ],
            // can't create because class don't exist
            [
                [
                    \Cachedecorator\Module::METHODS => [
                        'some_not_existing_class' => ['cached_method'],
                    ]
                ],
                'some_not_existing_class',
                false,
            ],
            // can't create because there is no such class in config
            [
                [
                    \Cachedecorator\Module::METHODS => [
                        get_class($service) => [],
                    ]
                ],
                'some_not_existing_class',
                false,
            ]
        ];
    }

    /**
     * when we call getConfig at the second time, $services->get('Config') should not be used
     */
    public function testGetConfigSecondCall()
    {
        $service = $this->getMock('stdClass', ['cached_method']);

        $config = [
            \Cachedecorator\Module::METHODS => [
                get_class($service) => ['cached_method'],
            ]
        ];
        $serviceLocator = $this->getServiceLocator($config);

        $result = $this->factory->canCreateServiceWithName($serviceLocator, null, get_class($service));
        $this->assertEquals($result, true);

        $result = $this->factory->canCreateServiceWithName($serviceLocator, null, get_class($service));
        $this->assertEquals($result, true);
    }

    /**
     * config does not contains \Cachedecorator\Module::METHODS entry
     *
     * @expectedException \Exception
     */
    public function testGetConfigWrongConfig()
    {
        $serviceLocator = $this->getServiceLocator([]);

        $this->factory->canCreateServiceWithName($serviceLocator, null, 'some_class');
    }

    public function testCreateServiceWithName()
    {
        $cacheStorage = 'some_cache_storage';
        $service = $this->getMock('\Zend\ServiceManager\ServiceLocatorAwareInterface', [
            'setServiceLocator', // impossible to test if this method was called
            'getServiceLocator',
        ]);
        $config = [get_class($service) => ['some_method']];

        $decoratorMock = $this->getMock('stdClass', ['setCacheStorage', 'setService', 'setAllowedMethods']);
        $decoratorMock->expects($this->once())
            ->method('setCacheStorage')
            ->with($this->equalTo($cacheStorage))
            ->will($this->returnSelf());
        $decoratorMock->expects($this->once())
            ->method('setService')
            ->with($this->equalTo($service))
            ->will($this->returnSelf());
        $decoratorMock->expects($this->once())
            ->method('setAllowedMethods')
            ->with($this->equalTo($config[get_class($service)]))
            ->will($this->returnSelf());

        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface', ['get', 'has']);
        $serviceLocator->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('Config'))
            ->will($this->returnValue([\Cachedecorator\Module::METHODS => $config]));
        $serviceLocator->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo(\Cachedecorator\Module::STORAGE))
            ->will($this->returnValue($cacheStorage));
        $serviceLocator->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo(\Cachedecorator\Module::DECORATOR_CLASS))
            ->will($this->returnValue($decoratorMock));

        $result = $this->factory->createServiceWithName($serviceLocator, null, get_class($service));
        $this->assertInstanceOf(get_class($decoratorMock), $result);
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new \Cachedecorator\AbstractFactory;
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->factory = null;

        parent::tearDown();
    }

    /**
     * @param $config
     * @return mixed
     */
    private function getServiceLocator($config)
    {
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface', ['get', 'has']);
        $serviceLocator->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Config'))
            ->will($this->returnValue($config));

        return $serviceLocator;
    }
}
