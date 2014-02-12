<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\Expression;
use SphinxSearch\Db\Sql\Exception;
use Zend\Db\Sql\Predicate\PredicateInterface;


class Match implements PredicateInterface
{


    /**
     * @var string
     */
    protected $specification = 'MATCH(%1$s)';


    protected $query = '';


    public function __construct($query = '')
    {
        $this->setQuery($query);
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

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
            array($this->specification, array($this->query), array(self::TYPE_VALUE))
        );
    }


//     /**
//      * @param $expression
//      * @return Expression
//      * @throws Exception\InvalidArgumentException
//      */
//     public function setExpression($expression)
//     {
//         if (!is_string($expression)) {
//             throw new Exception\InvalidArgumentException('Supplied expression must be a string.');
//         }
//         $this->expression = 'MATCH(' . $expression . ')';
//         return $this;
//     }

}