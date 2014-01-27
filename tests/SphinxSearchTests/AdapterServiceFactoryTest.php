<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace SphinxSearchTests;

class AdapterServiceFactoryTest extends AbstractTestCase
{

    public function testCreateService()
    {
        $factory = $this->getServiceLocator()->get('SphinxSearch\Db\Adapter\Adapter');

        $this->assertTrue(
            $factory->createService($this->getServiceLocator()),
            'ServiceLocator can\'t create adapter through factory.'
        );
    }

}
 