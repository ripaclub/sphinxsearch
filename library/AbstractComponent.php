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

use SphinxSearch\Exception;
use Zend\Db\Adapter\Driver\Mysqli\Mysqli as ZendMysqliDriver;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\AbstractSql;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\SqlInterface;

/**
 * Class AbstractComponent
 *
 * It represents every component capable that can execute SphinxQL queries.
 *
 */
abstract class AbstractComponent
{
    const QUERY_MODE_PREPARED = 'prepared'; //use prepared statement
    const QUERY_MODE_EXECUTE = 'execute'; //do not use prepared statement
    const QUERY_MODE_AUTO = 'auto'; //auto detect best available options (prepared mode preferred)

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
    protected $executeMode = self::QUERY_MODE_AUTO;

    /**
     * @param  string $flag
     * @throws \InvalidArgumentException
     * @return AbstractComponent
     */
    public function setQueryMode($flag)
    {
        $flags = [self::QUERY_MODE_AUTO, self::QUERY_MODE_PREPARED, self::QUERY_MODE_EXECUTE];
        if (!in_array($flag, $flags)) {
            throw new \InvalidArgumentException('Invalid execution mode. Must be one of: auto, prepared or query');
        }

        $this->executeMode = $flag;

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryMode()
    {
        return $this->executeMode;
    }

    /**
     * @param SqlInterface $sqlObject
     * @param bool|null $usePreparedStatement
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     * @throws Exception\InvalidArgumentException
     */
    public function executeSqlObject(SqlInterface $sqlObject, $usePreparedStatement = null)
    {
        if ($usePreparedStatement === null) {
            $usePreparedStatement = $this->isPreparedStatementUsed();
        }

        if ($usePreparedStatement && $sqlObject instanceof PreparableSqlInterface) {
            $statement = $this->getSql()->prepareStatementForSqlObject($sqlObject);
            return $statement->execute();
        }

        $sql = $this->getSql()->getSqlStringForSqlObject($sqlObject);
        return $this->getAdapter()->getDriver()->getConnection()->execute($sql);
    }

    /**
     * Are we using prepared statement?
     *
     * @return boolean
     */
    public function isPreparedStatementUsed()
    {
        if ($this->executeMode === self::QUERY_MODE_AUTO) {
            // Mysqli doesn't support client side prepared statement emulation
            if ($this->getAdapter()->getDriver() instanceof ZendMysqliDriver) {
                return false;
            }

            // By default, we use PDO prepared statement emulation
            return true;
        }

        if ($this->executeMode === self::QUERY_MODE_PREPARED) {
            return true;
        }

        return false;
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return \SphinxSearch\Db\Sql\Sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param string $sql
     * @return ResultInterface
     */
    public function execute($sql)
    {
        return $this->getAdapter()->getDriver()->getConnection()->execute($sql);
    }
}
