<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Adapter;

use Zend\Db\Adapter\Driver\Pdo\Pdo as ZendPdoDriver;
use \Zend\Db\Adapter\Driver\Mysqli\Mysqli as ZendMysqliDriver;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter as ZendDBAdapter;
use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use SphinxSearch\Db\Adapter\Driver\Pdo\Statement as PdoStatement;
use SphinxSearch\Db\Adapter\Exception\UnsupportedDriverException;

/**
 * Database adapter abstract service factory.
 *
 * Allows configuring several database instances (such as writer and reader).
 */
class AdapterAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Can we create an adapter by the requested name?
     *
     * @param  ServiceLocatorInterface $services
     * @param  string $name
     * @param  string $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);
        if (empty($config)) {
            return false;
        }

        return (
            isset($config[$requestedName])
            // && is_array($config[$requestedName]) // Omitted because could be a driver instance
            && !empty($config[$requestedName])
        );
    }

    /**
     * Create a DB adapter
     *
     * @param  ServiceLocatorInterface $services
     * @param  string                  $name
     * @param  string                  $requestedName
     * @throws Exception\UnsupportedDriverException
     * @return \Zend\Db\Adapter\Adapter
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);

        $platform = new SphinxQL();
        $adapter  = new ZendDBAdapter($config[$requestedName], $platform);
        $driver   = $adapter->getDriver();
        // Check driver
        if ($driver instanceof ZendPdoDriver && $driver->getDatabasePlatformName(ZendPdoDriver::NAME_FORMAT_CAMELCASE) == 'Mysql') {
            $adapter->getDriver()->registerStatementPrototype(new PdoStatement());
        } elseif (!$driver instanceof ZendMysqliDriver) {
            throw new UnsupportedDriverException(get_class($driver) . ' not supported. Use Zend\Db\Adapter\Driver\Pdo\Pdo or Zend\Db\Adapter\Driver\Mysqli\Mysqli');
        }

        $platform->setDriver($adapter->getDriver());

        return $adapter;
    }

    /**
     * Get db configuration, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config['sphinxql'])
            || !is_array($config['sphinxql'])
        ) {
            $this->config = array();
            return $this->config;
        }

        $config = $config['sphinxql'];
        if (!isset($config['adapters'])
            || !is_array($config['adapters'])
        ) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config['adapters'];
        return $this->config;
    }
}
