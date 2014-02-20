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
namespace SphinxSearch\Db\Sql;

use Zend\Db\Sql\Delete as ZendDelete;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Driver\DriverInterface;
use SphinxSearch\Db\Sql\Platform\ExpressionDecorator;

class Delete extends ZendDelete
{
    /**
     * Create from statement
     *
     * @param  string|TableIdentifier $table
     * @return Delete
     */
    public function from($table)
    {
        if ($table instanceof TableIdentifier) {
            list($table, ) = $table->getTableAndSchema(); // Ignore schema because it is not supported by SphinxQL
        }

        $this->table = $table;
        return $this;
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
}
