<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Sql;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Platform\Sql92;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\Update as ZendUpdate;
use Zend\Db\Sql\SqlInterface;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Predicate;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\ExpressionInterface;
use SphinxSearch\Db\Sql\Platform\ExpressionDecorator;

/**
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
    const VALUES_SET   = 'set';
    const OPTIONS_MERGE = 'merge';
    const OPTIONS_SET = 'set';
    /**@#-**/

    protected $specifications = array(
        self::SPECIFICATION_UPDATE => 'UPDATE %1$s SET %2$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s',
        self::SPECIFICATION_OPTION => array(
            'OPTION %1$s' => array(
                array(2 => '%1$s = %2$s', 'combinedby' => ', ')
            )
        ),
    );

    /**
     * @var array
     */
    protected $option = array();


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


    public function getRawState($key = null)
    {
        $rawState = array(
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set' => $this->set,
            'where' => $this->where,
            'option' => $this->option,
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
        $driver   = $adapter->getDriver();
        $platform = $adapter->getPlatform();
        $parameterContainer = $statementContainer->getParameterContainer();

        if (!$parameterContainer instanceof ParameterContainer) {
            $parameterContainer = new ParameterContainer();
            $statementContainer->setParameterContainer($parameterContainer);
        }

        $table = $this->table;
        $schema = null;

        // Create quoted table name to use in update processing
        if ($table instanceof TableIdentifier) {
            list($table, $schema) = $table->getTableAndSchema(); // NOTE: schema not supported by SphinxQL
        }

        $table = $platform->quoteIdentifier($table);

        $set = $this->set;
        if (is_array($set)) {
            $setSql = array();
            foreach ($set as $column => $value) {
                if ($value instanceof Predicate\Expression) {
                    $exprData = $this->processExpression($value, $platform, $driver);
                    $setSql[] = $platform->quoteIdentifier($column) . ' = ' . $exprData->getSql();
                    $parameterContainer->merge($exprData->getParameterContainer());
                } else {
                    $setSql[] = $platform->quoteIdentifier($column) . ' = ' . $driver->formatParameterName($column);
                    $parameterContainer->offsetSet($column, $value);
                }
            }
            $set = implode(', ', $setSql);
        }

        $sql = sprintf($this->specifications[self::SPECIFICATION_UPDATE], $table, $set);

        // Process where
        if ($this->where->count() > 0) {
            $whereParts = $this->processExpression($this->where, $platform, $driver, 'where');
            $parameterContainer->merge($whereParts->getParameterContainer());
            $sql .= ' ' . sprintf($this->specifications[self::SPECIFICATION_WHERE], $whereParts->getSql());
        }

        // Process option
        $optionParts = $this->processOption($platform, $driver, $parameterContainer);
        if (is_array($optionParts)) {
            $sql .= ' ' . $this->createSqlFromSpecificationAndParameters($this->specifications[self::SPECIFICATION_OPTION], $optionParts);
        }

        $statementContainer->setSql($sql);
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
        $schema = null;

        // Create quoted table name to use in update processing
        if ($table instanceof TableIdentifier) {
            list($table, $schema) = $table->getTableAndSchema();
        }

        $table = $adapterPlatform->quoteIdentifier($table);

        $set = $this->set;
        if (is_array($set)) {
            $setSql = array();
            foreach ($set as $column => $value) {
                if ($value instanceof Predicate\Expression) {
                    $exprData = $this->processExpression($value, $adapterPlatform);
                    $setSql[] = $adapterPlatform->quoteIdentifier($column) . ' = ' . $exprData->getSql();
                } elseif ($value === null) {
                    $setSql[] = $adapterPlatform->quoteIdentifier($column) . ' = NULL';
                } else {
                    $setSql[] = $adapterPlatform->quoteIdentifier($column) . ' = ' . $adapterPlatform->quoteValue($value);
                }
            }
            $set = implode(', ', $setSql);
        }

        $sql = sprintf($this->specifications[self::SPECIFICATION_UPDATE], $table, $set);
        if ($this->where->count() > 0) {
            $whereParts = $this->processExpression($this->where, $adapterPlatform, null, 'where');
            $sql .= ' ' . sprintf($this->specifications[self::SPECIFICATION_WHERE], $whereParts->getSql());
        }

        $optionParts = $this->processOption($adapterPlatform, null, null);
        if (is_array($optionParts)) {
            $sql .= ' ' . $this->createSqlFromSpecificationAndParameters($this->specifications[self::SPECIFICATION_OPTION], $optionParts);
        }

        return $sql;
    }

    protected function processExpression(ExpressionInterface $expression, PlatformInterface $platform, DriverInterface $driver = null, $namedParameterPrefix = null)
    {
        if ($expression instanceof ExpressionDecorator) {
            $expressionDecorator = $expression;
        } else {
            $expressionDecorator = new ExpressionDecorator($expression, $platform);
        }

        return parent::processExpression($expressionDecorator, $platform, $driver, $namedParameterPrefix);
    }

    protected function processOption(PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if (empty($this->option)) {
            return null;
        }
        // process options
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
                if ($driver) {
                    $parameterContainer->offsetSet('option_' .  $optName, $optValue);
                    $optionSql .= $driver->formatParameterName('option_' .  $optName);
                } else {
                    $optionSql .= $platform->quoteValue($optValue);
                }
            }
            $options[] = array($platform->quoteIdentifier($optName), $optionSql);
        }
        return array($options);
    }

}
