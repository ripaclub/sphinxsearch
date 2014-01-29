<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests\Db\Adapter;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

class AdapterAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceManager;

    /**
     * Set up service manager and database configuration.
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->serviceManager = new ServiceManager(new Config(array(
            'abstract_factories' => array('SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'),
        )));
        $this->serviceManager->setService('Config', array(
            'sphinxql' => array(
                'adapters' => array(
                    'SphinxSearch\Db\Adapter\One' => array(
                        'driver' => 'pdo_mysql',
                    ),
                    'SphinxSearch\Db\Adapter\Two' => array(
                        'driver' => 'pdo_mysql',
                    ),
                ),
            ),
        ));
    }

    /**
     * @return array
     */
    public function providerValidService()
    {
        return array(
            array('SphinxSearch\Db\Adapter\One'),
            array('SphinxSearch\Db\Adapter\Two'),
        );
    }

    /**
     * @return array
     */
    public function providerInvalidService()
    {
        return array(
            array('SphinxSearch\Db\Adapter\Unknown'),
        );
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @testdox Instantiates one or more adapters through their aliases
     */
    public function testValidService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('Zend\Db\Adapter\Adapter', $actual);
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
        $this->assertInstanceOf('Zend\Db\Adapter\Adapter', $actual);
    }

    /**
     * @return array
     */
    public function providerEmptyConfig()
    {
        return array();
    }

    /**
     * @param string $service
     * @dataProvider providerEmptyConfig
     */
    public function testEmptyConfig($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertFalse($actual);
    }

}
