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

use SphinxSearch\Db\Adapter\Exception\UnsupportedDriverException;
use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;

/**
 * Class AdapterAbstractServiceFactory
 *
 * Database adapter abstract service factory.
 *
 * Allows configuring several database instances (such as writer and reader).
 *
 */
class AdapterAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;
    
    /**
     * Get db configuration, if any
     *
     * @param  ServiceLocatorInterface|ContainerInterface $services
     * @return array
     */
    protected function getConfig($services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            $this->config = [];

            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config['sphinxql'])
            || !is_array($config['sphinxql'])
        ) {
            $this->config = [];

            return $this->config;
        }

        $config = $config['sphinxql'];
        if (!isset($config['adapters'])
            || !is_array($config['adapters'])
        ) {
            $this->config = [];

            return $this->config;
        }

        $this->config = $config['adapters'];

        return $this->config;
    }
    
    /**
     * @param  ServiceLocatorInterface|ContainerInterface $services
     * @param string $requestedName
     * @return boolean
     */
    protected function hasConfigForRequestedName($services, $requestedName)
    {
        // Workaround to avoid infinite loop when getService() tries to call $services->get('Config')
        if ($requestedName === 'Config') {
            return false;
        }
        
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
     * Can we create an adapter by the requested name?
     *
     * @param  ServiceLocatorInterface $services
     * @param  string $name
     * @param  string $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this->hasConfigForRequestedName($services, $requestedName);
    }

    /**
     * Create a DB adapter
     *
     * @param  ServiceLocatorInterface $services
     * @param  string $name
     * @param  string $requestedName
     * @throws Exception\UnsupportedDriverException
     * @return \Zend\Db\Adapter\Adapter
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);
        return AdapterServiceFactory::factory($config[$requestedName]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $this->hasConfigForRequestedName($container, $requestedName);
    }
    
    /**
     * {@inheritdoc}
     * @return \Zend\Db\Adapter\Adapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $this->getConfig($container);
        return AdapterServiceFactory::factory($config[$requestedName]); 
    }
    
    
}
