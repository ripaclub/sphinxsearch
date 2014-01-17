<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace SphinxSearch\Db\Adapter\Platform;

use Zend\Db\Adapter\Platform\PlatformInterface;

class SphinxQL implements PlatformInterface
{
    /**
     * @var \PDO
     */
    protected $resource;

    /**
     * @return string
     */
    public function getName()
    {
        return 'SphinxQL';
    }

    /**
     * Get quote identifier symbol
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return '';
    }

    /**
     * Quote identifier
     * @param  string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return $identifier;
    }

    /**
     * Quote identifier chain
     * @param string|string[] $identifierChain
     * @return string
     */
    public function quoteIdentifierChain($identifierChain)
    {
        return $identifierChain;
        /*
        $identifierChain = str_replace('`', '\\`', $identifierChain);
        if (is_array($identifierChain)) {
            $identifierChain = implode('`.`', $identifierChain);
        }
        return '`' . $identifierChain . '`';
        */
    }

    /**
     * Get quote value symbol
     * @return string
     */
    public function getQuoteValueSymbol()
    {
        return '\'';
    }

    /**
     * Quote value
     * @param  string $value
     * @return string
     */
    public function quoteValue($value)
    {
        if ($this->resource instanceof \PDO) {
            return $this->resource->quote($value);
        }
        return $this->quoteTrustedValue($value);
    }

    /**
     * Quote Trusted Value
     *
     * The ability to quote values without notices
     * @param $value
     * @return mixed
     */
    public function quoteTrustedValue($value)
    {
        if ($this->resource instanceof \PDO) {
            return $this->resource->quote($value);
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {

            if (round($value) == $value) {
                return (int)$value;
            }

            return sprintf('%F', $value);
        }

        return '\'' . addcslashes($value, "\x00\n\r\\'\"\x1a") . '\'';
    }

    /**
     * Quote value list
     * @param string|string[] $valueList
     * @return string
     */
    public function quoteValueList($valueList)
    {
        $valueList = str_replace('\'', '\\' . '\'', $valueList);
        if (is_array($valueList)) {
            $valueList = implode('\', \'', $valueList);
        }
        return '\'' . $valueList . '\'';
    }

    /**
     * Get identifier separator
     * @return string
     */
    public function getIdentifierSeparator()
    {
        return '.';
    }

    /**
     * Quote identifier in fragment
     * @param  string $identifier
     * @param  array $safeWords
     * @return string
     */
    public function quoteIdentifierInFragment($identifier, array $safeWords = array())
    {
        $parts = preg_split('#([\.\s\W])#', $identifier, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $i => $part) {
            if ($safeWords && in_array($part, $safeWords)) {
                continue;
            }
            switch ($part) {
                case ' ':
                case '.':
                case '*':
                case 'AS':
                case 'As':
                case 'aS':
                case 'as':
                    break;
                default:
                    // $parts[$i] = '`' . str_replace('`', '\\' . '`', $part) . '`';
            }
        }
        return implode('', $parts);
    }
}