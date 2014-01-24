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
use Zend\Db\Sql\SqlInterface;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Having;

/**
 *
 * @property Where $where
 * @property Having $having
 */
class Select extends AbstractSql implements SqlInterface, PreparableSqlInterface
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
            'SELECT %1$s %2$s FROM %3$s' => array(
                null,
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
                null
            ),
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
     * @var bool
     */
    protected $tableReadOnly = false;

    /**
     * @var string|array|TableIdentifier
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $columns = array(self::SQL_STAR);

    /**
     * @var Where
     */
    protected $where = null;

    /**
     * @var array
     */
    protected $order = array();

    /**
     * @var null|array
     */
    protected $group = null;

    /**
     * @var array
     */
    protected $withinGroupOrder = array();

    /**
     * @var null|string|array
     */
    protected $having = null;

    /**
     * @var int|null
     */
    protected $limit = null;

    /**
     * @var int|null
     */
    protected $offset = null;

    /**
     * @var array
     */
    protected $option = null;

    /**
     * @var array
     */
    protected $combine = array();

    /**
     * Constructor
     *
     * @param  null|string|array|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->from($table);
            $this->tableReadOnly = true;
        }

        $this->where = new Where();
        $this->having = new Having();
    }

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

        if (!is_string($table) && !is_array($table) && !$table instanceof TableIdentifier) {
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
     * @return Select
     */
    public function columns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @throws Exception\InvalidArgumentException
     * @return Select
     */
    public function where($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * @param string|array $group
     * @return Select
     */
    public function group($group)
    {
        if (is_array($group)) {
            foreach ($group as $o) {
                $this->group[] = $o;
            }
        } else {
            $this->group[] = $group;
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
     * Create where clause
     *
     * @param  Where|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @return Select
     */
    public function having($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Having) {
            $this->having = $predicate;
        } else {
            $this->having->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * @param string|array $order
     * @return Select
     */
    public function order($order)
    {
        if (is_string($order)) {
            if (strpos($order, ',') !== false) {
                $order = preg_split('#,\s+#', $order);
            } else {
                $order = (array) $order;
            }
        } elseif (!is_array($order)) {
            $order = array($order);
        }
        foreach ($order as $k => $v) {
            if (is_string($k)) {
                $this->order[$k] = $v;
            } else {
                $this->order[] = $v;
            }
        }
        return $this;
    }

    /**
     * @param int $limit
     * @return Select
     */
    public function limit($limit)
    {
        if (!is_numeric($limit)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects parameter to be numeric, "%s" given',
                __METHOD__,
                (is_object($limit) ? get_class($limit) : gettype($limit))
            ));
        }

        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return Select
     */
    public function offset($offset)
    {
        if (!is_numeric($offset)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects parameter to be numeric, "%s" given',
                __METHOD__,
                (is_object($offset) ? get_class($offset) : gettype($offset))
            ));
        }

        $this->offset = $offset;
        return $this;
    }

    /**
     * Set key/value pairs to option
     *
     * @param  array $values Associative array of key values
     * @param  string $flag One of the VALUES_* constants
     * @throws Exception\InvalidArgumentException
     * @return Select
     */
    public function option(array $values, $flag = self::VALUES_SET)
    {
        if ($values == null) {
            throw new Exception\InvalidArgumentException('option() expects an array of values');
        }

        if ($flag == self::VALUES_SET) {
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
     * @param Select $select
     * @param string $type
     * @param string $modifier
     * @return Select
     * @throws Exception\InvalidArgumentException
     */
    public function combine(Select $select, $type = self::COMBINE_UNION, $modifier = '')
    {
        if ($this->combine !== array()) {
            throw new Exception\InvalidArgumentException('This Select object is already combined and cannot be combined with multiple Selects objects');
        }
        $this->combine = array(
            'select' => $select,
            'type' => $type,
            'modifier' => $modifier
        );
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
                $this->order = array();
                break;
            case self::COMBINE:
                $this->combine = array();
                break;
        }
        return $this;
    }

    public function setSpecification($index, $specification)
    {
        if (!method_exists($this, 'process' . $index)) {
            throw new Exception\InvalidArgumentException('Not a valid specification name.');
        }
        $this->specifications[$index] = $specification;
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
     * Prepare statement
     *
     * @param AdapterInterface $adapter
     * @param StatementContainerInterface $statementContainer
     * @return void
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer)
    {
        // ensure statement has a ParameterContainer
        $parameterContainer = $statementContainer->getParameterContainer();
        if (!$parameterContainer instanceof ParameterContainer) {
            $parameterContainer = new ParameterContainer();
            $statementContainer->setParameterContainer($parameterContainer);
        }

        $sqls = array();
        $parameters = array();
        $platform = $adapter->getPlatform();
        $driver = $adapter->getDriver();

        foreach ($this->specifications as $name => $specification) {
            $parameters[$name] = $this->{'process' . $name}($platform, $driver, $parameterContainer, $sqls, $parameters);
            if ($specification && is_array($parameters[$name])) {
                $sqls[$name] = $this->createSqlFromSpecificationAndParameters($specification, $parameters[$name]);
            }
        }

        $sql = implode(' ', $sqls);

        $statementContainer->setSql($sql);
        return;
    }

    /**
     * Get SQL string for statement
     *
     * @param  null|PlatformInterface $adapterPlatform If null, defaults to Sql92
     * @return string
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null)
    {
        // get platform, or create default
        $adapterPlatform = ($adapterPlatform) ?: new AdapterSql92Platform;

        $sqls = array();
        $parameters = array();

        foreach ($this->specifications as $name => $specification) {
            $parameters[$name] = $this->{'process' . $name}($adapterPlatform, null, null, $sqls, $parameters);
            if ($specification && is_array($parameters[$name])) {
                $sqls[$name] = $this->createSqlFromSpecificationAndParameters($specification, $parameters[$name]);
            }
        }

        $sql = implode(' ', $sqls);
        return $sql;
    }

    /**
     * Returns whether the table is read only or not.
     *
     * @return bool
     */
    public function isTableReadOnly()
    {
        return $this->tableReadOnly;
    }

    protected function processStatementStart(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if ($this->combine !== array()) {
            return array('(');
        }
    }

    protected function processStatementEnd(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if ($this->combine !== array()) {
            return array(')');
        }
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

        if (!$this->table) {
            return null;
        }

        $table = $this->table;

         // create quoted table name to use in columns processing
        if ($table instanceof TableIdentifier) {
            list($table, $schema) = $table->getTableAndSchema();
        }

        //FIXME: check if sphinx allows to pass subselect
        if ($table instanceof Select) {
            $table = '(' . $this->processSubselect($table, $platform, $driver, $parameterContainer) . ')';
        } else {
            if (is_array($table)) {
                array_walk($table, function(&$item, $key) use ($platform) {
                    $item = $platform->quoteIdentifier($item);
                });
                $table = implode(', ', $table);
            } else {
                $table = $platform->quoteIdentifier($table);
            }
        }

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
                } else { //Allow prefix table in column name
                    $column = explode($separator, $column);
                    $columnName .= $platform->quoteIdentifier($column[0]) . $separator . $platform->quoteIdentifier($column[1]);
                }
            }

            // process As portion
            if (is_string($columnIndexOrAs)) {
                $columnAs = $platform->quoteIdentifier($columnIndexOrAs);
            } elseif (stripos($columnName, ' as ') === false && !is_string($column)) {
                $columnAs = 'Expression' . $expr++;
            }
            $columns[] = (isset($columnAs)) ? array($columnName, $columnAs) : array($columnName);
        }


        return array($columns, $table);

    }

    protected function processWhere(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if ($this->where->count() == 0) {
            return null;
        }
        $whereParts = $this->processExpression($this->where, $platform, $driver, $this->processInfo['paramPrefix'] . 'where');
        if ($parameterContainer) {
            $parameterContainer->merge($whereParts->getParameterContainer());
        }
        return array($whereParts->getSql());
    }

    protected function processGroup(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if ($this->group === null) {
            return null;
        }
        // process table columns
        $groups = array();
        foreach ($this->group as $column) {
            $columnSql = '';
            if ($column instanceof Expression) {
                $columnParts = $this->processExpression($column, $platform, $driver, $this->processInfo['paramPrefix'] . 'group');
                if ($parameterContainer) {
                    $parameterContainer->merge($columnParts->getParameterContainer());
                }
                $columnSql .= $columnParts->getSql();
            } else {
                $columnSql .= $platform->quoteIdentifierInFragment($column);
            }
            $groups[] = $columnSql;
        }
        return array($groups);
    }

    protected function processWithinGroupOrderBy(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
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

    protected function processHaving(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if ($this->having->count() == 0) {
            return null;
        }
        $whereParts = $this->processExpression($this->having, $platform, $driver, $this->processInfo['paramPrefix'] . 'having');
        if ($parameterContainer) {
            $parameterContainer->merge($whereParts->getParameterContainer());
        }
        return array($whereParts->getSql());
    }

    protected function processOrder(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if (empty($this->order)) {
            return null;
        }
        $orders = array();
        foreach ($this->order as $k => $v) {
            if ($v instanceof Expression) {
                /** @var $orderParts \Zend\Db\Adapter\StatementContainer */
                $orderParts = $this->processExpression($v, $platform, $driver);
                if ($parameterContainer) {
                    $parameterContainer->merge($orderParts->getParameterContainer());
                }
                $orders[] = array($orderParts->getSql());
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
                $orders[] = array($platform->quoteIdentifierInFragment($k), self::ORDER_DESCENDING);
            } else {
                $orders[] = array($platform->quoteIdentifierInFragment($k), self::ORDER_ASCENDING);
            }
        }
        return array($orders);
    }

    /**
     * @param PlatformInterface $platform
     * @param DriverInterface $driver
     * @param ParameterContainer $parameterContainer
     * @param $sqls
     * @param $parameters
     * @return null
     */
    protected function processLimitOffset(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null, &$sqls, &$parameters)
    {
        if ($this->limit === null && $this->offset === null) {
            return null;
        }

        $this->offset = (int) $this->offset;
        $this->limit  = (int) $this->limit;

        if ($driver) {
            $parameterContainer->offsetSet('limit', $this->limit, ParameterContainer::TYPE_INTEGER);
            $parameterContainer->offsetSet('offset', $this->offset, ParameterContainer::TYPE_INTEGER);
            return array(
                $driver->formatParameterName('offset'),
                $driver->formatParameterName('limit')
            );
        }

        return array(
            $this->offset,
            $this->limit
        );
    }

    protected function processOption(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if ($this->option === array()) {
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

    protected function processCombine(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if ($this->combine == array()) {
            return null;
        }

        $type = $this->combine['type'];
        if ($this->combine['modifier']) {
            $type .= ' ' . $this->combine['modifier'];
        }
        $type = strtoupper($type);

        if ($driver) {
            $sql = $this->processSubSelect($this->combine['select'], $platform, $driver, $parameterContainer);
            return array($type, $sql);
        }
        return array(
            $type,
            $this->processSubSelect($this->combine['select'], $platform)
        );
    }

    /**
     * Variable overloading
     *
     * @param  string $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'where':
                return $this->where;
            case 'having':
                return $this->having;
            default:
                throw new Exception\InvalidArgumentException('Not a valid magic property for this object');
        }
    }

    /**
     * __clone
     *
     * Resets the where object each time the Select is cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $this->where  = clone $this->where;
        $this->having = clone $this->having;
    }
}
