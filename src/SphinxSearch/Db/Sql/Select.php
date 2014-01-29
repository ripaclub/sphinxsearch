<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Sql;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Platform\Sql92 as AdapterSql92Platform;
use Zend\Db\Sql\AbstractSql;
use Zend\Db\Sql\Select as ZendSelect;
use Zend\Db\Sql\SqlInterface;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Having;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;

/**
 *
 * @property Where $where
 * @property Having $having
 */
class Select extends ZendSelect implements SqlInterface, PreparableSqlInterface
{
    /**#@+
     * Constant
     * @const
     */
    const SELECT = 'select';
    const COLUMNS = 'columns';
    const TABLE = 'table';
    const WHERE = 'where';
    const GROUP = 'group';
    const WITHINGROUPORDER = 'withingrouporder';
    const HAVING = 'having';
    const ORDER = 'order';
    const LIMIT = 'limit';
    const OFFSET = 'offset';
    const LIMITOFFSET = 'limitoffset';
    const OPTION = 'option';
    const SQL_STAR = '*';
    const ORDER_ASCENDING = 'ASC';
    const ORDER_DESCENDING = 'DESC';
    const COMBINE = 'combine';
    const COMBINE_UNION = 'union';
    const COMBINE_EXCEPT = 'except';
    const COMBINE_INTERSECT = 'intersect';

    const OPTIONS_MERGE = 'merge';
    const OPTIONS_SET = 'set';
    /**#@-*/

    /**
     * @var array Specifications
     */
    protected $specifications = array(
        'statementStart' => '%1$s',
        self::SELECT => array(
            'SELECT %1$s FROM %2$s' => array(
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
                null
            ),
            'SELECT %1$s' => array(
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', ')
            )
        ),
        self::WHERE  => 'WHERE %1$s',
        self::GROUP  => array(
            'GROUP BY %1$s' => array(
                array(1 => '%1$s', 'combinedby' => ', ')
            )
        ),
        self::WITHINGROUPORDER  => array(
            'WITHIN GROUP ORDER BY %1$s' => array(
                array(1 => '%1$s', 2 => '%1$s %2$s', 'combinedby' => ', ')
            )
        ),
        self::HAVING => 'HAVING %1$s',
        self::ORDER  => array(
            'ORDER BY %1$s' => array(
                array(1 => '%1$s', 2 => '%1$s %2$s', 'combinedby' => ', ')
            )
        ),
        self::LIMITOFFSET  => 'LIMIT %1$s,%2$s',
        self::OPTION       => array(
            'OPTION %1$s' => array(
                array(2 => '%1$s = %2$s', 'combinedby' => ', ')
            )
        ),
        'statementEnd' => '%1$s',
        self::COMBINE => '%1$s ( %2$s )',
    );

    /**
     * @var array
     */
    protected $withinGroupOrder = array();

    /**
     * @var array
     */
    protected $option = null;


    /**
     * Create from clause
     *
     * @param  string|array|TableIdentifier $table
     * @throws Exception\InvalidArgumentException
     * @return Select
     */
    public function from($table)
    {
        if ($this->tableReadOnly) {
            throw new Exception\InvalidArgumentException('Since this object was created with a table and/or schema in the constructor, it is read only.');
        }

        if (!is_string($table) && !is_array($table) && !$table instanceof TableIdentifier && !$table instanceof Select) {
            throw new Exception\InvalidArgumentException('$table must be a string, array, or an instance of TableIdentifier');
        }

        $this->table = $table;
        return $this;
    }

    /**
     * Specify columns from which to select
     *
     * Possible valid states:
     *
     *   array(*)
     *
     *   array(value, ...)
     *     value can be strings or Expression objects
     *
     *   array(string => value, ...)
     *     key string will be use as alias,
     *     value can be string or Expression objects
     *
     * @param  array $columns
     * @param  bool  $prefixColumnsWithTable
     * @return Select
     */
    public function columns(array $columns, $prefixColumnsWithTable = false)
    {
        $this->columns = $columns;

        if ($prefixColumnsWithTable) {
            throw new Exception\InvalidArgumentException('SphinxQL syntax does not support prefixing columns with table name');
        }

        return $this;
    }

    /**
     * @param string|array $order
     * @return Select
     */
    public function withinGroupOrder($withinGroupOrder)
    {
        if (is_string($withinGroupOrder)) {
            if (strpos($withinGroupOrder, ',') !== false) {
                $withinGroupOrder = preg_split('#,\s+#', $withinGroupOrder);
            } else {
                $withinGroupOrder = (array) $withinGroupOrder;
            }
        } elseif (!is_array($withinGroupOrder)) {
            $withinGroupOrder = array($withinGroupOrder);
        }
        foreach ($withinGroupOrder as $k => $v) {
            if (is_string($k)) {
                $this->withinGroupOrder[$k] = $v;
            } else {
                $this->withinGroupOrder[] = $v;
            }
        }
        return $this;
    }

    /**
     * Set key/value pairs to option
     *
     * @param  array $values Associative array of key values
     * @param  string $flag One of the OPTIONS_* constants
     * @throws Exception\InvalidArgumentException
     * @return Select
     */
    public function option(array $values, $flag = self::OPTIONS_SET)
    {
        if ($values == null) {
            throw new Exception\InvalidArgumentException('option() expects an array of values');
        }

        if ($flag == self::OPTIONS_SET) {
            $this->option = array();
        }

        foreach ($values as $k => $v) {
            if (!is_string($k)) {
                throw new Exception\InvalidArgumentException('option() expects a string for the value key');
            }
            $this->option[$k] = $v;
        }

        return $this;
    }

    /**
     * @param string $part
     * @return Select
     * @throws Exception\InvalidArgumentException
     */
    public function reset($part)
    {
        switch ($part) {
            case self::TABLE:
                if ($this->tableReadOnly) {
                    throw new Exception\InvalidArgumentException(
                        'Since this object was created with a table and/or schema in the constructor, it is read only.'
                    );
                }
                $this->table = null;
                break;
            case self::COLUMNS:
                $this->columns = array();
                break;
            case self::WHERE:
                $this->where = new Where;
                break;
            case self::GROUP:
                $this->group = null;
                break;
            case self::WITHINGROUPORDER:
                $this->withinGroupOrder = array();
            case self::HAVING:
                $this->having = new Having;
                break;
            case self::LIMIT:
                $this->limit = null;
                break;
            case self::OPTION:
                $this->option = array();
                break;
            case self::OFFSET:
                $this->offset = null;
                break;
            case self::ORDER:
                $this->order = null;
                break;
            case self::COMBINE:
                $this->combine = array();
                break;
        }
        return $this;
    }

    public function getRawState($key = null)
    {
        $rawState = array(
            self::TABLE      => $this->table,
            self::COLUMNS    => $this->columns,
            self::WHERE      => $this->where,
            self::ORDER      => $this->order,
            self::GROUP      => $this->group,
            self::WITHINGROUPORDER => $this->withinGroupOrder,
            self::HAVING     => $this->having,
            self::LIMIT      => $this->limit,
            self::OPTION     => $this->option,
            self::OFFSET     => $this->offset,
            self::COMBINE    => $this->combine
        );
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Process the select part
     *
     * @param PlatformInterface $platform
     * @param DriverInterface $driver
     * @param ParameterContainer $parameterContainer
     * @return null|array
     */
    protected function processSelect(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        $expr = 1;

        $separator = $platform->getIdentifierSeparator();

        // process table columns
        $columns = array();
        foreach ($this->columns as $columnIndexOrAs => $column) {

            $columnName = '';
            if ($column === self::SQL_STAR) {
                $columns[] = array(self::SQL_STAR);
                continue;
            }

            if ($column instanceof Expression) {
                $columnParts = $this->processExpression(
                    $column,
                    $platform,
                    $driver,
                    $this->processInfo['paramPrefix'] . ((is_string($columnIndexOrAs)) ? $columnIndexOrAs : 'column')
                );
                if ($parameterContainer) {
                    $parameterContainer->merge($columnParts->getParameterContainer());
                }
                $columnName .= $columnParts->getSql();
            } else {
                if (strpos($column, $separator) === false) {
                    $columnName .= $platform->quoteIdentifier($column);
                } else { // Allow prefix table in column name
                    $column = explode($separator, $column);
                    $columnName .= $platform->quoteIdentifier($column[0]) . $separator . $platform->quoteIdentifier($column[1]);
                }
            }

            // process As portion
            $columnAs = null;
            if (is_string($columnIndexOrAs)) {
                $columnAs = $platform->quoteIdentifier($columnIndexOrAs);
            } elseif (stripos($columnName, ' as ') === false && !is_string($column)) {
                $columnAs = 'Expression' . $expr++;
            }
            $columns[] = (isset($columnAs)) ? array($columnName, $columnAs) : array($columnName);
        }


        if ($this->table) {

            $table = $this->table;

            // create quoted table name to use in FROM clause
            if ($table instanceof TableIdentifier) {
                list($table, $schema) = $table->getTableAndSchema();
            }

            if ($table instanceof Select) {
                $table = '(' . $this->processSubselect($table, $platform, $driver, $parameterContainer) . ')';
            } else {

                if (is_string($table)) {
                    if (strpos($table, ',') !== false) {
                        $table = preg_split('#,\s+#', $table);
                    } else {
                        $table = (array) $table;
                    }
                } elseif (!is_array($table)) {
                    $table = array($table);
                }

                array_walk($table, function(&$item, $key) use ($platform) {
                    $item = $platform->quoteIdentifier($item);
                });
                $table = implode(', ', $table);
            }

            return array($columns, $table);
        }

        return array($columns);
    }

    protected function processWithinGroupOrder(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if (empty($this->withinGroupOrder)) {
            return null;
        }
        $withinGroupOrders = array();
        foreach ($this->withinGroupOrder as $k => $v) {
            if ($v instanceof Expression) {
                /** @var $parts \Zend\Db\Adapter\StatementContainer */
                $orderParts = $this->processExpression($v, $platform, $driver);
                if ($parameterContainer) {
                    $parameterContainer->merge($orderParts->getParameterContainer());
                }
                $withinGroupOrders[] = array($orderParts->getSql());
                continue;
            }
            if (is_int($k)) {
                if (strpos($v, ' ') !== false) {
                    list($k, $v) = preg_split('# #', $v, 2);
                } else {
                    $k = $v;
                    $v = self::ORDER_ASCENDING;
                }
            }
            if (strtoupper($v) == self::ORDER_DESCENDING) {
                $withinGroupOrders[] = array($platform->quoteIdentifierInFragment($k), self::ORDER_DESCENDING);
            } else {
                $withinGroupOrders[] = array($platform->quoteIdentifierInFragment($k), self::ORDER_ASCENDING);
            }
        }
        return array($withinGroupOrders);
    }

    /**
     * @param PlatformInterface $platform
     * @param DriverInterface $driver
     * @param ParameterContainer $parameterContainer
     * @param $sqls
     * @param $parameters
     * @return null
     */
    protected function processLimitOffset(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if ($this->limit === null && $this->offset === null) {
            return null;
        }

        $offset = (int) $this->offset;
        $limit  = (int) $this->limit;

        if ($driver) {
            $parameterContainer->offsetSet('limit', $limit, ParameterContainer::TYPE_INTEGER);
            $parameterContainer->offsetSet('offset', $offset, ParameterContainer::TYPE_INTEGER);
            return array(
                $driver->formatParameterName('offset'),
                $driver->formatParameterName('limit')
            );
        }

        return array($offset, $limit);
    }

    protected function processOption(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if (!$this->option) {
            return null;
        }
        // process table columns
        $options = array();
        foreach ($this->option as $optName => $optValue) {
            $optionSql = '';
            if ($optValue instanceof Expression) {
                $optionParts = $this->processExpression($optValue, $platform, $driver, $this->processInfo['paramPrefix'] . 'option');
                if ($parameterContainer) {
                    $parameterContainer->merge($optionParts->getParameterContainer());
                }
                $optionSql .= $optionParts->getSql();
            } else {
                $optionSql .= $platform->quoteValue($optValue);
            }
            $options[] = array($platform->quoteIdentifier($optName), $optionSql);
        }
        return array($options);
    }
}
