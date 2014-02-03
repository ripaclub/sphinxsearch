<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\TestAsset;

use SphinxSearch\AbstractComponent;
use Zend\Db\Adapter\Adapter as ZendDBAdapter;
use SphinxSearch\Db\Sql\Sql;

class ConcreteComponentAsset extends AbstractComponent
{

    /**
     * @param ZendDBAdapter $adapter
     * @param Sql $sql
     */
    public function __construct(ZendDBAdapter $adapter, Sql $sql = null)
    {
        $this->adapter = $adapter;
        $this->sql     = $sql ? $sql : new Sql($adapter);
    }


}