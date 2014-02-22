<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014,
 *              Leonardo Di Donato <leodidonato at gmail dot com>,
 *              Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Adapter;

use Zend\Db\Adapter\Driver\Pdo\Pdo as ZendPdoDriver;
use \Zend\Db\Adapter\Driver\Mysqli\Mysqli as ZendMysqliDriver;
use Zend\Db\Adapter\Adapter as ZendDBAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use SphinxSearch\Db\Adapter\Driver\Pdo\Statement as PdoStatement;
use SphinxSearch\Db\Adapter\Exception\UnsupportedDriverException;

class AdapterServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param  ServiceLocatorInterface              $serviceLocator
     * @throws Exception\UnsupportedDriverException
     * @return \Zend\Db\Adapter\Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $platform = new SphinxQL();
        $adapter  = new ZendDBAdapter($config['sphinxql'], $platform);
        $driver   = $adapter->getDriver();
        // Check driver
        if ($driver instanceof ZendPdoDriver &&
            $driver->getDatabasePlatformName(ZendPdoDriver::NAME_FORMAT_CAMELCASE) == 'Mysql') {
            $driver->registerStatementPrototype(new PdoStatement());
        } elseif (!$driver instanceof ZendMysqliDriver) {
            $class = get_class($driver);
            throw new UnsupportedDriverException(
                $class . ' not supported. Use Zend\Db\Adapter\Driver\Pdo\Pdo or Zend\Db\Adapter\Driver\Mysqli\Mysqli'
            );
        }

        $platform->setDriver($adapter->getDriver());

        return $adapter;
    }
}
