<?php
/**
 * User: leodido
 * Date: 28/01/14
 * Time: 0.09
 */

namespace SphinxSearchTests;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

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
    public function setUp()
    {
        $this->serviceManager = new ServiceManager(new ServiceManagerConfig(array(
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
     * @testdox Creates one or more adapters through their aliases
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
     * @testdox Does not create an invalid/unknow adapter
     */
    public function testInvalidService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('Zend\Db\Adapter\Adapter', $actual);
    }

}
