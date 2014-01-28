<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace SphinxSearchTests;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractTestCase
 * @package SphinxSearchTests
 */
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceManager
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setUp()
    {
        $this->setServiceLocator(Bootstrap::getServiceManager());
    }

    /**
     * @return array|object
     */
    public function getConfig()
    {
        return $this->getServiceLocator()->get('Config');
    }

    /**
     * @param int $index
     * @return null|array
     */
    public function getDriverConfig($index = 0)
    {

        $driver = null;
        $counter = 0;
        foreach ($this->getConfig()['sphinxql']['drivers'] as $alias => $driver) {
            if ($index == $counter) {
                return array(
                    'alias'     =>  $alias,
                    'driver'    =>  $driver
                );
            }
            ++$counter;
        }

        return null;
    }

} 