<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015
 *              Leo Di Donato <leodidonato at gmail dot com>,
 *              Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Adapter;

use SphinxSearch\Db\Adapter\Driver\Pdo\Statement as PdoStatement;
use SphinxSearch\Db\Adapter\Exception\UnsupportedDriverException;
use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use Zend\Db\Adapter\Adapter as ZendDBAdapter;
use Zend\Db\Adapter\Driver\Mysqli\Mysqli as ZendMysqliDriver;
use Zend\Db\Adapter\Driver\Pdo\Pdo as ZendPdoDriver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;

/**
 * Class AdapterServiceFactory
 *
 * Database adapter service factory.
 *
 * Allows configuring a single database instance.
 *
 */
class AdapterServiceFactory implements FactoryInterface
{
    /**
     * Default configuration key
     * 
     * @var string
     */
    protected $configKey = 'sphinxql';
    
    /**
     * @param array|\Zend\Db\Adapter\Driver\DriverInterface $config
     * @throws UnsupportedDriverException
     * @return \Zend\Db\Adapter\Adapter
     */
    public static function factory($config)
    {
        $platform = new SphinxQL();
        $adapter = new ZendDBAdapter($config, $platform);
        $driver = $adapter->getDriver();
        // Check driver
        if ($driver instanceof ZendPdoDriver &&
            $driver->getDatabasePlatformName(ZendPdoDriver::NAME_FORMAT_CAMELCASE) == 'Mysql'
            ) {
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
    
    /**
     * Create db adapter service
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @throws Exception\UnsupportedDriverException
     * @return \Zend\Db\Adapter\Adapter
     * @deprecated Use __invoke() instead.
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return self::factory($serviceLocator->get('Config')[$this->configKey]);
    }
    
    /**
     * Create db adapter service
     * 
     * {@inheritdoc}
     * @return \Zend\Db\Adapter\Adapter
     * @throws UnsupportedDriverException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return self::factory($options ? $options : $container->get('Config')[$this->configKey]);
    }
}
