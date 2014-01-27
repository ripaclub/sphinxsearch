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

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{

    use ServiceLocatorAwareTrait;

    public function setUp()
    {
        $this->setServiceLocator(Bootstrap::getServiceManager());
    }
    
} 