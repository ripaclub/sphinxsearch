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

        $this->assertTrue(
            $factory->createService($this->getServiceLocator()) instanceof ZendDBAdapter,
            'ServiceLocator can\'t create adapter through factory.'
        );

        // Through SM registration

        $sm = clone $this->getServiceLocator();

        $sm->setFactory('__TEST_FACTORY_ALAIS__', $factory);

        $this->assertTrue(
            $sm->get('__TEST_FACTORY_ALAIS__') instanceof ZendDBAdapter,
            'ServiceLocator can\'t create adapter through factory.'
        );
    }

}
 