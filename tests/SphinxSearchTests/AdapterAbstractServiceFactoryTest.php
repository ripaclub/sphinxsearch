<?php
/**
 * User: leodido
 * Date: 28/01/14
 * Time: 0.09
 */

namespace SphinxSearchTests;

use SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory;
use Zend\Db\Adapter\Adapter as ZendDBAdapter;

class AdapterAbstractServiceFactoryTest extends AbstractTestCase {

    public function testCreateServiceWithName()
    {
        $config = $this->getDriverConfig();
        $factory = new AdapterAbstractServiceFactory();
        // Assertion
        $this->assertTrue(
            $factory->canCreateServiceWithName($this->getServiceLocator(), null, $config['alias']),
            'ServiceLocator can\'t create adapter through abstract service factory.'
        );
        // Assertion
        $this->assertTrue(
            $factory->createServiceWithName($this->getServiceLocator(), null, $config['alias']) instanceof ZendDBAdapter,
            'ServiceLocator created adapter is not an instance of SphinxSearch\Db\Adapter.'
        );
    }


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
            array('SphinxSearch\Db\Adapter\One'),
        );
    }

    /**
     * @return array
     */
    public function providerInvalidService()
    {
        return array(
            array('SphinxSearch\Db\Adapter\OUnknown'),
        );
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     */
    public function testValidService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('SphinxSearch\Db\Adapter', $actual);
    }

    /**
     * @param string $service
     * @dataProvider providerInvalidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testInvalidService($service)
    {
        $actual = $this->serviceManager->get($service);
    }



}
