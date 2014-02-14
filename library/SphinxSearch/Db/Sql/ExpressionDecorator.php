<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Sql;

use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Expression;

class ExpressionDecorator implements ExpressionInterface
{

    /**
     * @var bool
     */
    protected static $floatAsLiteral = true;

    /**
     * @var ExpressionInterface
     */
    protected $subject;

    /**
     * @param ExpressionInterface $expression
     * @return ExpressionDecorator
     */
    public function setSubject(ExpressionInterface $expression)
    {
        $this->subject = $expression;
        return $this;
    }

    /**
     * @return ExpressionInterface
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param bool $flag
     */
    public static function setFloatAsLiteral($flag = true)
    {
        self::$floatAsLiteral = (bool) $flag;
    }

    /**
     * @return boolean
     */
    public static function getFloatAsLiteral()
    {
        return self::$floatAsLiteral;
    }

    /**
     *
     * @return array of array|string should return an array in the format:
     *
     * array (
     *    // a sprintf formatted string
     *    string $specification,
     *
     *    // the values for the above sprintf formatted string
     *    array $values,
     *
     *    // an array of equal length of the $values array, with either TYPE_IDENTIFIER or TYPE_VALUE for each value
     *    array $types,
     * )
     *
     */
    public function getExpressionData()
    {
        $expressionData = $this->subject->getExpressionData();

        if (self::getFloatAsLiteral()) {
            foreach ($expressionData as &$expressionPart) {
                $parametersCount = count($expressionPart[1]);
                for ($i=0; $i<$parametersCount; $i++) {
                    if (is_float($expressionPart[1][$i]) && $expressionPart[2][$i] == Expression::TYPE_VALUE) {
                        $expressionPart[1][$i] = sprintf('%F', $expressionPart[1][$i]);
                        $expressionPart[2][$i] = Expression::TYPE_LITERAL;
                    }
                }
            }
        }

        return $expressionData;
    }

}