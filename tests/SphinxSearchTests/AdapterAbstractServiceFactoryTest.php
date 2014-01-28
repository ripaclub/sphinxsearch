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
            'ServiceLocator created adapter is not an instance of Zend\Db\Adapeter\Adapter.'
        );
    }
    
}
 