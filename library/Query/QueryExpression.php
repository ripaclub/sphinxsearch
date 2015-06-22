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
namespace SphinxSearch\Query;

/**
 * Class QueryExpression
 */
class QueryExpression implements QueryInterface
{
    /**
     * @const
     */
    const PLACEHOLDER = '?';

    /**
     * @var string
     */
    protected $expression = '';

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param string $expression
     * @param string|array $parameters
     */
    public function __construct($expression = '', $parameters = null)
    {
        if ($expression) {
            $this->setExpression($expression);
        }
        if ($parameters) {
            $this->setParameters($parameters);
        }
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     * @return QueryExpression
     * @throws Exception\InvalidArgumentException
     */
    public function setExpression($expression)
    {
        if (!is_string($expression)) {
            throw new Exception\InvalidArgumentException('Supplied expression must be a string.');
        }
        $this->expression = $expression;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param $parameters
     * @return QueryExpression
     * @throws Exception\InvalidArgumentException
     */
    public function setParameters($parameters)
    {
        if (!is_scalar($parameters) && !is_array($parameters)) {
            throw new Exception\InvalidArgumentException('Expression parameters must be a scalar or array.');
        }
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return string
     * @throws Exception\RuntimeException
     */
    public function toString()
    {
        $parameters = (is_scalar($this->parameters)) ? [$this->parameters] : $this->parameters;

        $parametersCount = count($parameters);
        if ($parametersCount == 0 && strpos($this->expression, self::PLACEHOLDER) !== false) {
            // if there are no parameters, but there is a placeholder
            $parametersCount = substr_count($this->expression, self::PLACEHOLDER);
            $parameters = array_fill(0, $parametersCount, null);
        }

        foreach ($parameters as &$parameter) {
            $parameter = static::escapeString($parameter);
        }

        // assign locally, escaping % signs
        $expression = str_replace('%', '%%', $this->expression);

        if ($parametersCount > 0) {
            $count = 0;
            $expression = str_replace(self::PLACEHOLDER, '%s', $expression, $count);
            if ($count !== $parametersCount) {
                throw new Exception\RuntimeException(
                    'The number of replacements in the expression does not match the number of parameters'
                );
            }
        }

        return vsprintf($expression, $parameters);
    }

    /**
     * Escape a string value for Sphinx Query Syntax
     *
     * @link http://sphinxsearch.com/docs/2.2.2/api-func-escapestring.html
     * @link https://github.com/php/pecl-search_engine-sphinx/blob/master/sphinx.c#L1531
     *
     * @param  string $value
     * @return string
     */
    public static function escapeString($value)
    {
        return str_replace(
            ['\\', '(', ')', '|', '-', '!', '@', '~', '"', '&', '/'],
            ['\\\\', '\\(', '\\)', '\\|', '\\-', '\\!', '\\@', '\\~', '\\"', '\\&', '\\/'],
            $value
        );
    }
}
