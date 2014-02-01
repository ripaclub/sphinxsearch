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
use SphinxSearch\Db\Adapter\Driver\Pdo\Statement as PdoStatement;
use SphinxSearch\Db\Adapter\Driver\Mysqli\Statement as MysqliStatement;
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

        if ($driver instanceof \Zend\Db\Adapter\Driver\Pdo\Pdo) {
            $adapter->getDriver()->registerStatementPrototype(new PdoStatement());
        } elseif (!$driver instanceof \Zend\Db\Adapter\Driver\Mysqli\Mysqli) {

        } else {
            throw new UnsupportedDriverException(get_class($driver) . ' not supported. Use Zend\Db\Adapter\Driver\Pdo\Pdo or Zend\Db\Adapter\Driver\Mysqli\Mysqli');
        }

        $platform->setDriver($adapter->getDriver());


        return $adapter;
    }
}
