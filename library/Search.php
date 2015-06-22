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
namespace SphinxSearch;

use SphinxSearch\Db\Sql\Select;
use SphinxSearch\Db\Sql\Show;
use SphinxSearch\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter as ZendDBAdapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Where;

/**
 * Class Search
 */
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
        $this->sql = $sql ? $sql : new Sql($adapter);
    }

    /**
     * @return ResultSetInterface
     */
    public function getResultSetPrototype()
    {
        return $this->resultSetPrototype;
    }

    /**
     * @param  string|array $index
     * @param  Where|\Closure|string|array $where
     * @return ResultSet
     */
    public function search($index, $where = null)
    {
        $select = $this->getSql()->select($index);

        if ($where instanceof \Closure) {
            $where($select);
        } elseif ($where !== null) {
            $select->where($where);
        }

        return $this->searchWith($select);
    }

    /**
     * @param  Select $select
     * @return ResultSetInterface
     */
    public function searchWith(Select $select)
    {
        $result = $this->executeSqlObject($select);
        // Build result set
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * @param string $show
     * @param string $like
     * @return ResultInterface
     */
    protected function show($show, $like)
    {
        $show = $this->getSql()->show()->show($show)->like($like);
        return $this->executeSqlObject($show);
    }

    /**
     * @param string $like
     * @return array
     */
    public function showMeta($like = null)
    {
        $result = $this->show(Show::SHOW_META, $like);
        $return = [];

        foreach ($result as $row) {
            $return[$row['Variable_name']] = $row['Value'];
        }

        return $return;
    }

    /**
     * @param string $like
     * @return array
     */
    public function showStatus($like = null)
    {
        $result = $this->show(Show::SHOW_STATUS, $like);
        $return = [];
        foreach ($result as $row) {
            $return[$row['Counter']] = $row['Value'];
        }

        return $return;
    }
}
