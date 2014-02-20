<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014,
 *              Leonardo Di Donato <leodidonato at gmail dot com>
 *              Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch;

use Zend\Db\Adapter\Adapter as ZendDBAdapter;
use Zend\Db\Sql\Insert;
use SphinxSearch\Db\Sql\Sql;
use SphinxSearch\Db\Sql\Update;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\TableIdentifier;

class Indexer extends AbstractComponent
{
    /**
     * @param ZendDBAdapter $adapter
     * @param Sql           $sql
     */
    public function __construct(ZendDBAdapter $adapter, Sql $sql = null)
    {
        $this->adapter = $adapter;
        $this->sql     = $sql ? $sql : new Sql($adapter);
    }

    /**
     * @return \SphinxSearch\Indexer
     */
    public function beginTransaction()
    {
        $this->getAdapter()->getDriver()->getConnection()->beginTransaction();

        return $this;
    }

    /**
     * @return \SphinxSearch\Indexer
     */
    public function commit()
    {
        $this->getAdapter()->getDriver()->getConnection()->commit();

        return $this;
    }

    /**
     * @return \SphinxSearch\Indexer
     */
    public function rollback()
    {
        $this->getAdapter()->getDriver()->getConnection()->rollback();

        return $this;
    }

    /**
     * @param  string|TableIdentifier $index
     * @param  array                  $values
     * @param  bool                   $replace
     * @return number
     */
    public function insert($index, array $values, $replace = false)
    {
        $sqlObject = $replace ? $this->sql->replace($index) : $this->sql->insert($index);
        $sqlObject->values($values);

        return $this->insertWith($sqlObject);
    }

    /**
     * @param  Insert $insert
     * @return number
     */
    public function insertWith(Insert $insert)
    {
        $result = $this->executeSqlObject($insert);

        return $result->getAffectedRows();
    }

    /**
     * @param  string|TableIdentifier      $index
     * @param  array                       $values
     * @param  Where|\Closure|string|array $where
     * @return number
     */
    public function update($index, array $values, $where = null)
    {
        $update = $this->sql->update($index);
        $update->set($values);

        if ($where instanceof \Closure) {
            $where($update);
        } elseif ($where !== null) {
            $update->where($where);
        }

        return $this->updateWith($update);
    }

    /**
     * @param  Update $update
     * @return number
     */
    public function updateWith(Update $update)
    {
        $result = $this->executeSqlObject($update);

        return $result->getAffectedRows();
    }

    /**
     * @param  string|TableIdentifier      $index
     * @param  Where|\Closure|string|array $where
     * @return number
     */
    public function delete($index, $where)
    {
        $delete = $this->sql->delete($index);

        if ($where instanceof \Closure) {
            $where($delete);
        } elseif ($where !== null) {
            $delete->where($where);
        }

        return $this->deleteWith($delete);
    }

    /**
     * @param  Delete $delete
     * @return number
     */
    public function deleteWith(Delete $delete)
    {
        $result = $this->executeSqlObject($delete);

        return $result->getAffectedRows();
    }
}
