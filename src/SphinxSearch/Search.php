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
use SphinxSearch\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\ResultSet\ResultSet;

class Search {

    /**
     * @var ZendDBAdapter
     */
    protected $adapter;

    /**
     * @var ResultSetInterface
     */
    protected $resultSetPrototype;

    /**
     * @var Sql
     */
    protected $sql;

    /**
     * @param ZendDBAdapter $adapter
     * @param ResultSetInterface $resultSetPrototype
     * @param Sql $sql
     */
    public function __construct(ZendDBAdapter $adapter, ResultSetInterface $resultSetPrototype = null, Sql $sql = null)
    {
        $this->adapter = $adapter;
        // result prototype
        $this->resultSetPrototype = ($resultSetPrototype) ? : new ResultSet();
        $this->sql     = $sql ? $sql : new Sql($adapter);
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return \Zend\Db\ResultSet\ResultSetInterface
     */
    public function getResultSetPrototype()
    {
        return $this->resultSetPrototype;
    }

    /**
     * @return Sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param string $index
     * @return Select
     */
    public function getSelect($index = null)
    {
        return $this->sql->select($index);
    }

    /**
     * @param Select $select
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function searchWith(Select $select)
    {
         // prepare and execute
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        // build result set
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        return $resultSet;
    }

}
