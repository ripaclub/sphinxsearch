<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch;

use Zend\Db\Sql\AbstractSql;
use \Zend\Db\Adapter\Driver\Mysqli\Mysqli as ZendMysqliDriver;

abstract class AbstractComponent
{
    const EXECUTE_MODE_PREPARED = 'prepared';
    const EXECUTE_MODE_QUERY    = 'query';
    const EXECUTE_MODE_AUTO     = 'auto';

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;


    /**
     * @var \SphinxSearch\Db\Sql\Sql
     */
    protected $sql;

    /**
     * @var string
     */
    protected $executeMode = self::EXECUTE_MODE_AUTO;


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
     * @return \SphinxSearch\Db\Sql\Sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param string $flag
     * @throws \InvalidArgumentException
     * @return \SphinxSearch\AbstractComponent
     */
    public function setExecutionMode($flag)
    {
        if (!in_array($flag, array(self::EXECUTE_MODE_AUTO, self::EXECUTE_MODE_PREPARED, self::EXECUTE_MODE_QUERY))) {
            throw new \InvalidArgumentException('Invalid execution mode. Must be one of: auto, prepared or query');
        }

        $this->executeMode = $flag;
        return $this;
    }

    /**
     * @return string
     */
    public function getExecutionMode()
    {
        return $this->executeMode;
    }

    /**
     * Are we using prepared statement?
     *
     * @return boolean
     */
    public function usePreparedStatement()
    {
        if ($this->executeMode === self::EXECUTE_MODE_AUTO) {
            // Mysqli doesn't support client side prepared statement emulation
            if ($this->getAdapter()->getDriver() instanceof ZendMysqliDriver) {
                return false;
            }

            // By default, we use PDO prepared statement emulation
            return true;
        }

        if ($this->executeMode === self::EXECUTE_MODE_PREPARED) {
            return true;
        }

        return false;
    }

    /**
     * @param AbstractSql $sqlObject
     * @param string $usePreparedStatement
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function executeSqlObject(AbstractSql $sqlObject, $usePreparedStatement = null)
    {
        if ($usePreparedStatement === null) {
            $usePreparedStatement = $this->usePreparedStatement();
        }

        if ($usePreparedStatement) {
            $statement = $this->getSql()->prepareStatementForSqlObject($sqlObject);
            return $statement->execute();
        }

        $sql = $this->getSql()->getSqlStringForSqlObject($sqlObject);

        return $this->getAdapter()->getDriver()->getConnection()->execute($sql);
    }

}