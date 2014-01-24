<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace SphinxSearch;
use Zend\Db\Adapter\Adapter as ZendDBAdapter;
use SphinxSearch\Db\Sql\Sql;
use SphinxSearch\Db\Sql\Replace;

class Indexer {

    /**
     * @var ZendDBAdapter
     */
    protected $adapter;

    /**
     * @var Sql
     */
    protected $sql;

    /**
     * @param ZendDBAdapter $adapter
     * @param Sql $sql
     */
    public function __construct(ZendDBAdapter $adapter, Sql $sql = null)
    {
        $this->adapter = $adapter;

        $this->sql     = $sql ? $sql : new Sql($adapter);
    }

    /**
     * @return Sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    public function beginTransaction()
    {
        $this->adapter->driver->getConnection()->beginTransaction();
        return $this;
    }

    public function commit()
    {
        $this->adapter->driver->getConnection()->commit();
        return $this;
    }

    public function rollback()
    {
        $this->adapter->driver->getConnection()->rollback();
        return $this;
    }

    public function update(array $data, $index, $upsert = true)
    {

    }



}