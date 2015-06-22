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
namespace SphinxSearch\Db\Sql;

use SphinxSearch\Db\Sql\Platform\ExpressionDecorator;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Platform\Sql92;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\SqlInterface;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Update as ZendUpdate;
use Zend\Db\Sql\Where;
use Zend\Version\Version;

/**
 * Class Update
 *
 * @property Where $where
 */
class Update extends ZendUpdate implements SqlInterface, PreparableSqlInterface
{

    /**@#++
     * @const
     */
    const SPECIFICATION_UPDATE = 'update';
    const SPECIFICATION_WHERE = 'where';
    const SPECIFICATION_OPTION = 'option';

    const VALUES_MERGE = 'merge';
    const VALUES_SET = 'set';
    const OPTIONS_MERGE = 'merge';
    const OPTIONS_SET = 'set';
    /**@#-**/

    protected $specifications = [
        self::SPECIFICATION_UPDATE => 'UPDATE %1$s SET %2$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s',
        self::SPECIFICATION_OPTION => [
            'OPTION %1$s' => [
                [2 => '%1$s = %2$s', 'combinedby' => ', ']
            ]
        ],
    ];

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var array
     */
    protected $option = [];

    /**
     * Specify table for statement
     *
     * @param  string|TableIdentifier $table
     * @return Update
     */
    public function table($table)
    {
        if ($table instanceof TableIdentifier) {
            $table = $table->getTable(); // Ignore schema because it is not supported by SphinxQL
        }

        $this->table = $table;

        return $this;
    }

    /**
     * Set key/value pairs to option
     *
     * @param  array $values Associative array of key values
     * @param  string $flag One of the OPTIONS_* constants
     * @throws Exception\InvalidArgumentException
     * @return Update
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

    public function getRawState($key = null)
    {
        $rawState = parent::getRawState();
        $rawState['option'] = $this->option;
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Prepare statement
     *
     * @param  AdapterInterface $adapter
     * @param  StatementContainerInterface $statementContainer
     * @return void
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer)
    {
        $driver = $adapter->getDriver();
        $platform = $adapter->getPlatform();
        $parameterContainer = $statementContainer->getParameterContainer();

        if (!$parameterContainer instanceof ParameterContainer) {
            $parameterContainer = new ParameterContainer();
            $statementContainer->setParameterContainer($parameterContainer);
        }

        $table = $this->table;
        $table = $platform->quoteIdentifier($table);

        $set = $this->set;

        $setSql = [];
        foreach ($set as $column => $value) {
            if ($value instanceof Predicate\Expression) {
                $exprData = $this->processExpression($value, $platform, $driver, $parameterContainer);
                $setSql[] = $platform->quoteIdentifier($column) . ' = ' . $exprData;
            } else {
                $setSql[] = $platform->quoteIdentifier($column) . ' = ' . $driver->formatParameterName($column);
                $parameterContainer->offsetSet($column, $value);
            }
        }
        $set = implode(', ', $setSql);

        $sql = sprintf($this->specifications[self::SPECIFICATION_UPDATE], $table, $set);

        // Process where
        if ($this->where->count() > 0) {
            $whereParts = $this->processExpression($this->where, $platform, $driver, $parameterContainer, 'where');
            $sql .= ' ' . sprintf($this->specifications[self::SPECIFICATION_WHERE], $whereParts);
        }

        // Process option
        $optionParts = $this->processOption($platform, $driver, $parameterContainer);
        if (is_array($optionParts)) {
            $sql .= ' ' . $this->createSqlFromSpecificationAndParameters(
                $this->specifications[self::SPECIFICATION_OPTION],
                $optionParts
            );
        }

        $statementContainer->setSql($sql);
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

    /**
     * Get SQL string for statement
     *
     * @param  null|PlatformInterface $adapterPlatform If null, defaults to Sql92
     * @return string
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null)
    {
        $adapterPlatform = ($adapterPlatform) ? : new Sql92;
        $table = $this->table;
        $table = $adapterPlatform->quoteIdentifier($table);

        $set = $this->set;

        $setSql = [];
        foreach ($set as $col => $val) {
            if ($val instanceof Predicate\Expression) {
                $exprData = $this->processExpression($val, $adapterPlatform);
                $setSql[] = $adapterPlatform->quoteIdentifier($col) . ' = ' . $exprData;
            } elseif ($val === null) {
                $setSql[] = $adapterPlatform->quoteIdentifier($col) . ' = NULL';
            } else {
                $setSql[] = $adapterPlatform->quoteIdentifier($col) . ' = ' . $adapterPlatform->quoteValue($val);
            }
        }
        $set = implode(', ', $setSql);

        $sql = sprintf($this->specifications[self::SPECIFICATION_UPDATE], $table, $set);
        if ($this->where->count() > 0) {
            $whereParts = $this->processExpression($this->where, $adapterPlatform, null, null, 'where');
            $sql .= ' ' . sprintf($this->specifications[self::SPECIFICATION_WHERE], $whereParts);
        }

        $optionParts = $this->processOption($adapterPlatform, null, null);
        if (is_array($optionParts)) {
            $sql .= ' ' . $this->createSqlFromSpecificationAndParameters(
                $this->specifications[self::SPECIFICATION_OPTION],
                $optionParts
            );
        }

        return $sql;
    }
}
