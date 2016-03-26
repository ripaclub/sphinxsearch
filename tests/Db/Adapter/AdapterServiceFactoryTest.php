<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Adapter;

use SphinxSearch\Db\Adapter\AdapterServiceFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

/**
 * Class AdapterServiceFactoryTest
 */
class AdapterServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceManager;

    /**
     * @return array
     */
    public function providerValidService()
    {
        return [
            ['SphinxSearch\Db\Adapter\Adapter']
        ];
    }

    /**
     * @return array
     */
    public function providerInvalidService()
    {
        return [
            ['SphinxSearch\Db\Adapter\Unknown'],
        ];
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @testdox Instantiates an adapter
     */
    public function testValidService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('\Zend\Db\Adapter\Adapter', $actual);
    }

    /**
     * @param string $service
     * @dataProvider providerInvalidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @testdox Does not instantiate an invalid/unknow adapter
     */
    public function testInvalidService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('\Zend\Db\Adapter\Adapter', $actual);
    }

    /**
     * @testdox Launch exception when driver is not supported
     */
    public function testUnsupportedDriver()
    {
        // testing Mysqli driver
        $mockDriver = $this->getMock(
            '\Zend\Db\Adapter\Driver\Pdo\Pdo',
            ['getDatabasePlatformName'],
            [],
            '',
            false
        );
        $mockDriver->expects($this->any())->method('getDatabasePlatformName')->will($this->returnValue('NotMysql'));

        $sManager = new ServiceManager();
        $sManager->setService(
            'Config',
            [
                'sphinxql' => $mockDriver
            ]
        );

        //Test exception by factory
        $factory = new AdapterServiceFactory();

        $this->setExpectedException('\SphinxSearch\Db\Adapter\Exception\UnsupportedDriverException');
        $factory->createService($sManager);
    }

    /**
     * Set up service manager and database configuration.
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $serviceManagerConfig = new Config([
            'factories' => [
                'SphinxSearch\Db\Adapter\Adapter' => 'SphinxSearch\Db\Adapter\AdapterServiceFactory'
            ],
            'alias' => [
                'sphinxql' => 'SphinxSearch\Db\Adapter\Adapter'
            ]
        ]); 
        
        $this->serviceManager = new ServiceManager();
        $serviceManagerConfig->configureServiceManager($this->serviceManager);
        $this->serviceManager->setService(
            'Config',
            [
                'sphinxql' => [
                    'driver' => 'pdo_mysql',
                ],
            ]
        );
    }

}
