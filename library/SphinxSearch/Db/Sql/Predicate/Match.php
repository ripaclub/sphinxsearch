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
namespace SphinxSearch\Db\Sql\Predicate;

use SphinxSearch\Db\Sql\Exception;
use Zend\Db\Sql\Predicate\PredicateInterface;
use SphinxSearch\Query\QueryExpression;
use SphinxSearch\Query\QueryInterface;

class Match implements PredicateInterface
{
    /**
     * @var string
     */
    protected $specification = 'MATCH(%1$s)';

    /**
     * @var QueryInterface
     */
    protected $query = null;

    /**
     * @param  string                             $expression
     * @param  string                             $parameters
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($expression = '', $parameters = null)
    {
        if (!$expression instanceof QueryInterface) {
            if (!is_string($expression)) {
                throw new Exception\InvalidArgumentException(
                    'Supplied expression must be a string or an instance of SphinxSearch\Query\QueryInterface'
                );
            }

            $expression = new QueryExpression($expression, $parameters);
        }
        //TODO:
//         elseif ($parameters && method_exists($expression, 'setParameters')) {
//             $expression->setParameters($parameters);
//         }

        $this->setQuery($expression);
    }

    /**
     * @param  QueryInterface $query
     * @return Match
     */
    public function setQuery(QueryInterface $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return QueryExpression
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param  string $specification
     * @return self
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        return array(
            array($this->specification, array($this->query->toString()), array(self::TYPE_VALUE))
        );
    }
}
