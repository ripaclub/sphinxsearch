<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Adapter;

use SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AdapterAbstractServiceFactoryTest
 */
class AdapterAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
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
            ['SphinxSearch\Db\Adapter\One'],
            ['SphinxSearch\Db\Adapter\Two'],
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
     * @testdox Instantiates one or more adapters through their aliases
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
     * @testdox Does not instantiate invalid/unknow adapters
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
                'sphinxql' => [
                    'adapters' => [
                        'SphinxSearch\Db\Adapter\Unsupported' => $mockDriver
                    ]
                ]
            ]
        );

        //Test exception by factory
        $factory = new AdapterAbstractServiceFactory();

        $this->assertTrue(
            $factory->canCreateServiceWithName(
                $sManager,
                'SphinxSearch\Db\Adapter\Unsupported',
                'SphinxSearch\Db\Adapter\Unsupported'
            )
        );

        $this->setExpectedException('\SphinxSearch\Db\Adapter\Exception\UnsupportedDriverException');
        $factory->createServiceWithName(
            $sManager,
            'SphinxSearch\Db\Adapter\Unsupported',
            'SphinxSearch\Db\Adapter\Unsupported'
        );
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @testdox Launch exception when there isn't a configuration node
     */
    public function testNullConfig($service)
    {
        $sManagerConfig = new Config(
            [
                'abstract_factories' => ['SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'],
            ]
        ); 
        $sManager = new ServiceManager();
        $sManagerConfig->configureServiceManager($sManager);
        $sManager->get($service);
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @testdox Launch exception when configuration node is empty
     */
    public function testEmptyConfig($service)
    {
        $sManagerConfig = new Config(
            [
                'abstract_factories' => ['SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'],
            ]
        );
        $sManager = new ServiceManager();
        $sManagerConfig->configureServiceManager($sManager);
        $sManager->setService('Config', []);
        $sManager->get($service);
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @testdox Launch exception when sphinxql configuration node is empty
     */
    public function testEmptySphinxQLConfig($service)
    {
        $sManagerConfig = new Config(
            [
                'abstract_factories' => ['SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'],
            ]
        );
        $sManager = new ServiceManager();
        $sManagerConfig->configureServiceManager($sManager);
        $sManager->setService(
            'Config',
            [
                'sphinxql' => []
            ]
        );
        $sManager->get($service);
    }

    /**
     * Set up service manager and database configuration.
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $sManagerConfig = new Config(
            [
                'abstract_factories' => ['SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'],
            ]
        );
        $this->serviceManager = new ServiceManager();
        $sManagerConfig->configureServiceManager($this->serviceManager);
        $this->serviceManager->setService(
            'Config',
            [
                'sphinxql' => [
                    'adapters' => [
                        'SphinxSearch\Db\Adapter\One' => [
                            'driver' => 'pdo_mysql',
                        ],
                        'SphinxSearch\Db\Adapter\Two' => [
                            'driver' => 'pdo_mysql',
                        ],
                    ],
                ],
            ]
        );
    }
}
