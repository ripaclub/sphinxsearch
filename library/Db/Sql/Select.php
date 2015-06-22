<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015
 *              Leonardo Di Donato <leodidonato at gmail dot com>,
 *              Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Sql;

use SphinxSearch\Db\Sql\Platform\ExpressionDecorator;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\StatementContainer;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Having;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\Select as ZendSelect;
use Zend\Db\Sql\SqlInterface;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Where;
use Zend\Version\Version;

/**
 * Class Select
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

    const OPTIONS_MERGE = 'merge';
    const OPTIONS_SET = 'set';
    /**#@-*/

    /**
     * Specification not supported by Sphinx are removed
     *
     * @var array Specifications
     */
    protected $specifications = [
        self::SELECT => [
            'SELECT %1$s FROM %2$s' => [
                [1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '],
                null
            ],
            'SELECT %1$s' => [
                [1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', ']
            ]
        ],
        self::WHERE => 'WHERE %1$s',
        self::GROUP => [
            'GROUP BY %1$s' => [
                [1 => '%1$s', 'combinedby' => ', ']
            ]
        ],
        self::WITHINGROUPORDER => [
            'WITHIN GROUP ORDER BY %1$s' => [
                [1 => '%1$s', 2 => '%1$s %2$s', 'combinedby' => ', ']
            ]
        ],
        self::HAVING => 'HAVING %1$s',
        self::ORDER => [
            'ORDER BY %1$s' => [
                [1 => '%1$s', 2 => '%1$s %2$s', 'combinedby' => ', ']
            ]
        ],
        self::LIMITOFFSET => 'LIMIT %1$s,%2$s',
        self::OPTION => [
            'OPTION %1$s' => [
                [2 => '%1$s = %2$s', 'combinedby' => ', ']
            ]
        ]
    ];

    /**
     * @var array
     */
    protected $withinGroupOrder = [];

    /**
     * @var array
     */
    protected $option = [];

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
            throw new Exception\InvalidArgumentException(
                'Since this object was created with a table and/or schema in the constructor, it is read only.'
            );
        }

        if (!is_string($table) &&
            !is_array($table) &&
            !$table instanceof TableIdentifier &&
            !$table instanceof Select
        ) {
            throw new Exception\InvalidArgumentException(
                '$table must be a string, array, an instance of TableIdentifier, or an instance of Select'
            );
        }

        if ($table instanceof TableIdentifier) {
            $table = $table->getTable(); // Ignore schema because it is not supported by SphinxQL
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
     * @param  array $columns
     * @param  bool $prefixColumnsWithTable
     * @return Select
     * @throws Exception\InvalidArgumentException
     */
    public function columns(array $columns, $prefixColumnsWithTable = false)
    {
        $this->columns = $columns;

        if ($prefixColumnsWithTable) {
            throw new Exception\InvalidArgumentException(
                'SphinxQL syntax does not support prefixing columns with table name'
            );
        }

        return $this;
    }

    /**
     * @param  string|array $withinGroupOrder
     * @return Select
     */
    public function withinGroupOrder($withinGroupOrder)
    {
        if (is_string($withinGroupOrder)) {
            if (strpos($withinGroupOrder, ',') !== false) {
                $withinGroupOrder = preg_split('#,\s+#', $withinGroupOrder);
            } else {
                $withinGroupOrder = (array)$withinGroupOrder;
            }
        } elseif (!is_array($withinGroupOrder)) {
            $withinGroupOrder = [$withinGroupOrder];
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
    public function option(array $values, $flag = self::OPTIONS_MERGE)
    {
        if ($values == null) {
            throw new Exception\InvalidArgumentException('option() expects an array of values');
        }

        if ($flag == self::OPTIONS_SET) {
            $this->option = [];
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
     * @param  string $part
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

            case self::WITHINGROUPORDER:
                $this->withinGroupOrder = [];
                break;

            case self::OPTION:
                $this->option = [];
                break;

            case self::ORDER:
                $this->order = [];
                break;

            default:
                parent::reset($part);
        }

        return $this;
    }

    public function getRawState($key = null)
    {
        $rawState = [
            self::TABLE => $this->table,
            self::COLUMNS => $this->columns,
            self::WHERE => $this->where,
            self::ORDER => $this->order,
            self::GROUP => $this->group,
            self::WITHINGROUPORDER => $this->withinGroupOrder,
            self::HAVING => $this->having,
            self::LIMIT => $this->limit,
            self::OPTION => $this->option,
            self::OFFSET => $this->offset,
        ];

        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Process the select part
     *
     * @param  PlatformInterface $platform
     * @param  DriverInterface $driver
     * @param  ParameterContainer $parameterContainer
     * @return null|array
     */
    protected function processSelect(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        $expr = 1;

        // process table columns
        $columns = [];
        foreach ($this->columns as $columnIndexOrAs => $column) {

            $colName = '';
            if ($column === self::SQL_STAR) {
                $columns[] = [self::SQL_STAR]; // Sphinx doesn't not support prefix column with table, yet
                continue;
            }

            if ($column instanceof Expression) {
                $columnParts = $this->processExpression(
                    $column,
                    $platform,
                    $driver,
                    $parameterContainer,
                    $this->processInfo['paramPrefix'] . ((is_string($columnIndexOrAs)) ? $columnIndexOrAs : 'column')
                );
                $colName .= $columnParts;
            } else {
                // Sphinx doesn't not support prefix column with table, yet
                $colName .= $platform->quoteIdentifier($column);
            }

            // process As portion
            $columnAs = null;
            if (is_string($columnIndexOrAs)) {
                $columnAs = $columnIndexOrAs;
            } elseif (stripos($colName, ' as ') === false && !is_string($column)) {
                $columnAs = 'Expression' . $expr++;
            }

            $columns[] = isset($columnAs) ? [$colName, $platform->quoteIdentifier($columnAs)] : [$colName];
        }

        if ($this->table) {
            $tableList = $this->table;

            if (is_string($tableList) && strpos($tableList, ',') !== false) {
                $tableList = preg_split('#,\s+#', $tableList);
            } elseif (!is_array($tableList)) {
                $tableList = [$tableList];
            }

            foreach ($tableList as &$table) {

                // create quoted table name to use in FROM clause
                if ($table instanceof Select) {
                    $table = '(' . $this->processSubselect($table, $platform, $driver, $parameterContainer) . ')';
                } else {
                    $table = $platform->quoteIdentifier($table);
                }
            }

            $tableList = implode(', ', $tableList);

            return [$columns, $tableList];
        }

        return [$columns];
    }


    /**
     * {@inheritdoc}
     */
    protected function processExpression(
        ExpressionInterface $expression,
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null,
        $namedParameterPrefix = null
    ) {
        if ($expression instanceof ExpressionDecorator) {
            $expressionDecorator = $expression;
        } else {
            $expressionDecorator = new ExpressionDecorator($expression, $platform);
        }

        return parent::processExpression($expressionDecorator, $platform, $driver, $parameterContainer, $namedParameterPrefix);
    }

    protected function processWithinGroupOrder(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if (empty($this->withinGroupOrder)) {
            return null;
        }
        $withinGroupOrders = [];
        foreach ($this->withinGroupOrder as $k => $v) {
            if ($v instanceof Expression) {
                /** @var $parts \Zend\Db\Adapter\StatementContainer */
                $orderParts = $this->processExpression($v, $platform, $driver, $parameterContainer);
                $withinGroupOrders[] = [$orderParts];
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
                $withinGroupOrders[] = [$platform->quoteIdentifierInFragment($k), self::ORDER_DESCENDING];
            } else {
                $withinGroupOrders[] = [$platform->quoteIdentifierInFragment($k), self::ORDER_ASCENDING];
            }
        }

        return [$withinGroupOrders];
    }

    /**
     * @param  PlatformInterface $platform
     * @param  DriverInterface $driver
     * @param  ParameterContainer $parameterContainer
     * @return array|null
     */
    protected function processLimitOffset(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if ($this->limit === null && $this->offset === null) {
            return null;
        }

        $offset = (int)$this->offset;
        $limit = (int)$this->limit;

        if ($driver && $parameterContainer) {
            $parameterContainer->offsetSet('limit', $limit, ParameterContainer::TYPE_INTEGER);
            $parameterContainer->offsetSet('offset', $offset, ParameterContainer::TYPE_INTEGER);

            return [
                $driver->formatParameterName('offset'),
                $driver->formatParameterName('limit')
            ];
        }

        return [$offset, $limit];
    }

    protected function processOption(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if (empty($this->option)) {
            return null;
        }
        // process options
        $options = [];
        foreach ($this->option as $optName => $optValue) {
            $optionSql = '';
            if ($optValue instanceof Expression) {
                $parameterPrefix = $this->processInfo['paramPrefix'] . 'option';
                $optionParts = $this->processExpression($optValue, $platform, $driver, $parameterContainer, $parameterPrefix);
                $optionSql .= $optionParts;
            } else {
                if ($driver && $parameterContainer) {
                    $parameterContainer->offsetSet('option_' . $optName, $optValue);
                    $optionSql .= $driver->formatParameterName('option_' . $optName);
                } else {
                    $optionSql .= $platform->quoteValue($optValue);
                }
            }
            $options[] = [$platform->quoteIdentifier($optName), $optionSql];
        }

        return [$options];
    }
}