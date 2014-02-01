<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace SphinxSearch\Db\Adapter;

use Zend\Db\Adapter\Adapter as ZendDBAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use SphinxSearch\Db\Adapter\Driver\Pdo\Statement;
use SphinxSearch\Db\Adapter\Exception\UnsupportedDriverException;

class AdapterServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $platform = new SphinxQL();
        $adapter  = new ZendDBAdapter($config['sphinxql'], $platform);
        $driver   = $adapter->getDriver();

        if (!$driver instanceof \Zend\Db\Adapter\Driver\Pdo\Pdo) {
            throw new UnsupportedDriverException('Only Zend\Db\Adapter\Driver\Pdo\Pdo supported at moment');
        }

        $platform->setDriver($adapter->getDriver());
        $adapter->getDriver()->registerStatementPrototype(new Statement());

        return $adapter;
    }
}
