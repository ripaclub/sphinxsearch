<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace SphinxSearchTests;

use SphinxSearch\Db\Adapter\AdapterServiceFactory;
use Zend\Db\Adapter\Adapter as ZendDBAdapter;

class AdapterServiceFactoryTest extends AbstractTestCase
{
    public function testCreateService()
    {
        $factory = new AdapterServiceFactory();
        // Assertion
        $this->assertTrue(
            $factory->createService($this->getServiceLocator()) instanceof ZendDBAdapter,
            'ServiceLocator can\'t create adapter through service factory.'
        );

        // Through SM registration
        // NOTE: clone the service locator because it has not been destroyed in a tear down method
        $smanager = clone $this->getServiceLocator();
        $smanager->setFactory('__TEST_FACTORY_ALIAS__', $factory);
        // Assertion
        $this->assertTrue(
            $smanager->get('__TEST_FACTORY_ALIAS__') instanceof ZendDBAdapter,
            'ServiceLocator can\'t create adapter through service factory.'
        );
    }

}
 