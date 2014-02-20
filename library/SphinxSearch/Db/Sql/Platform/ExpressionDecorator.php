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
namespace SphinxSearch\Db\Sql\Platform;

use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Platform\PlatformInterface;
use SphinxSearch\Db\Adapter\Platform\SphinxQL;

class ExpressionDecorator implements ExpressionInterface
{
    /**
     * @var ExpressionInterface
     */
    protected $subject;

    /**
     * @var SphinxQL
     */
    protected $platform;

    /**
     * @param ExpressionInterface $subject
     */
    public function __construct(ExpressionInterface $subject, SphinxQL $platform)
    {
        $this->setSubject($subject);
        $this->platform = $platform;
    }

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

        foreach ($expressionData as &$expressionPart) {
            if (!is_array($expressionPart)) {
                continue;
            }
            $parametersCount = count($expressionPart[1]);
            for ($i = 0; $i < $parametersCount; $i++) {
                if ($this->platform->isFloatConversionEnabled() &&
                    is_float($expressionPart[1][$i]) &&
                    $expressionPart[2][$i] == Expression::TYPE_VALUE) {
                    $expressionPart[1][$i] = $this->platform->toFloatSinglePrecision($expressionPart[1][$i]);
                    $expressionPart[2][$i] = Expression::TYPE_LITERAL;
                }
                if (is_bool($expressionPart[1][$i]) && $expressionPart[2][$i] == Expression::TYPE_VALUE) {
                    $expressionPart[1][$i] = (int) $expressionPart[1][$i];
                }
            }
        }

        return $expressionData;
    }
}
