<?php
namespace Cachedecorator\Tests;

/**
 * @covers Cachedecorator\Decorator
 */
class DecoratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Zend\Cache\Storage\StorageInterface Cache storage */
    private $storage;

    /** @var string Method to test */
    private $testedMethod = 'someTestedMethod';

    /**
     * Tests that method, mentioned in setAllowedMethods() is cached
     */
    public function testAllowedMethod()
    {
        $resultData = 'someResultData';
        $inputData = 'someInputData';

        $service = $this->getService();

        // cached method will be called only once
        $service->expects($this->once())
            ->method($this->testedMethod)
            ->with($this->equalTo($inputData))
            ->will($this->returnValue($resultData));

        $decorator = $this->getDecorator($service, $this->storage, [$this->testedMethod]);

        $firstResult = $decorator->{$this->testedMethod}($inputData);
        $this->assertEquals($firstResult, $resultData);

        // after first call data is in cache
        $key = get_class($service) . '.' . $this->testedMethod . '.' . md5(json_encode([$inputData]));
        $resultInCache = $this->storage->getItem($key);
        $this->assertEquals($resultInCache, $resultData);

        // if we call cached method twice, we get data from cache
        $resultFromCache = $decorator->{$this->testedMethod}($inputData);
        $this->assertEquals($resultFromCache, $resultData);
    }

    /**
     * Tests that method, not mentioned in setAllowedMethods() is not cached
     */
    public function testDisabledMethod()
    {
        $resultData = 'someResultData';
        $inputData = 'someInputData';

        $storage = $this->getMock('stdClass', ['getItem', 'setItem']);
        $storage->expects($this->never())->method('getItem');
        $storage->expects($this->never())->method('setItem');

        $service = $this->getService();

        $service->expects($this->exactly(2))
            ->method($this->testedMethod)
            ->with($this->equalTo($inputData))
            ->will($this->returnValue($resultData));

        // no methods are cached
        $decorator = $this->getDecorator($service, $storage, []);

        $firstResult = $decorator->{$this->testedMethod}($inputData);
        $this->assertEquals($firstResult, $resultData);

        $secondResult = $decorator->{$this->testedMethod}($inputData);
        $this->assertEquals($secondResult, $resultData);
    }

    /**
     * Tests that Iterator is returned as array after caching
     */
    public function testIteratorData()
    {
        $inputData = 'someInputData';
        $resultData = ['firstValue', 'secondValue'];

        $service = $this->getService();

        // cached method will be called only once
        $service->expects($this->once())
            ->method($this->testedMethod)
            ->with($this->equalTo($inputData))
            ->will($this->returnValue(new \ArrayIterator($resultData)));

        $decorator = $this->getDecorator($service, $this->storage, [$this->testedMethod]);

        $firstResult = $decorator->{$this->testedMethod}($inputData);
        $this->assertEquals($firstResult, $resultData);

        // after first call data is in cache
        $key = get_class($service) . '.' . $this->testedMethod . '.' . md5(json_encode([$inputData]));
        $resultInCache = $this->storage->getItem($key);
        $this->assertEquals($resultInCache, $resultData);

        // if we call cached method twice, we get data from cache
        $resultFromCache = $decorator->{$this->testedMethod}($inputData);
        $this->assertEquals($resultFromCache, $resultData);
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->storage = \Zend\Cache\StorageFactory::factory([
            'adapter' => 'memory'
        ]);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->storage = null;

        parent::tearDown();
    }

    /**
     * @param $service
     * @param $allowedMethods
     * @return \Cachedecorator\Decorator
     */
    protected function getDecorator($service, $storage, $allowedMethods)
    {
        return (new \Cachedecorator\Decorator)
            ->setCacheStorage($storage)
            ->setService($service)
            ->setAllowedMethods($allowedMethods);
    }

    /**
     * @return mixed
     */
    protected function getService()
    {
        return $this->getMock('stdClass', [$this->testedMethod]);
    }
}
