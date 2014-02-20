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
namespace SphinxSearch;

use Zend\Db\Adapter\Adapter as ZendDBAdapter;
use SphinxSearch\Db\Sql\Sql;
use SphinxSearch\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Where;

class Search extends AbstractComponent
{
    /**
     * @var ResultSetInterface
     */
    protected $resultSetPrototype;

    /**
     * @param ZendDBAdapter $adapter
     * @param ResultSetInterface $resultSetPrototype
     * @param Sql $sql
     */
    public function __construct(ZendDBAdapter $adapter, ResultSetInterface $resultSetPrototype = null, Sql $sql = null)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = ($resultSetPrototype) ? : new ResultSet();
        $this->sql     = $sql ? $sql : new Sql($adapter);
    }

    /**
     * @return \Zend\Db\ResultSet\ResultSetInterface
     */
    public function getResultSetPrototype()
    {
        return $this->resultSetPrototype;
    }

    /**
     * @param string|array $index
     * @param Where|\Closure|string|array $where
     * @return ResultSet
     */
    public function search($index, $where = null)
    {
        $select = $this->sql->select($index);

        if ($where instanceof \Closure) {
            $where($select);
        } elseif ($where !== null) {
            $select->where($where);
        }

        return $this->searchWith($select);
    }

    /**
     * @param Select $select
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function searchWith(Select $select)
    {
        $result = $this->executeSqlObject($select);
        // Build result set
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        return $resultSet;
    }
}
