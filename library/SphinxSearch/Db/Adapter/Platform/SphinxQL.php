<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Adapter\Platform;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Platform\Mysql;
use Zend\Db\Adapter\Driver\DriverInterface;

class SphinxQL extends Mysql implements PlatformInterface
{

    protected $floatConversion = true;

    /**
     * @return string
     */
    public function getName()
    {
        return 'SphinxQL';
    }

    /**
     * Quotes value
     *
     * @param  string $value
     * @return string
     */
    public function quoteValue($value)
    {
        if (is_int($value)) {
            return (string) $value;
        } elseif (is_float($value)) {
            return $this->floatConversion ? $this->toFloatSinglePrecision($value) : (string) $value;
        } elseif (is_null($value)) {
            return 'NULL'; // Not supported by SphinxQL, but included for consistency with prepared statement behavior
        }

        return parent::quoteValue($value);
    }

    /**
     * Quotes trusted value
     *
     * The ability to quote values without notices
     *
     * @param $value
     * @return mixed
     */
    public function quoteTrustedValue($value)
    {
        if (is_int($value)) {
            return (string) $value;
        } elseif (is_float($value)) {
            return $this->floatConversion ? $this->toFloatSinglePrecision($value) : (string) $value;
        } elseif (is_null($value)) {
            return 'NULL'; // Not supported by SphinxQL, but included for consistency with prepared statement behavior
        }

        return parent::quoteTrustedValue($value);
    }

    /**
     * Converts PHP floats (double precision) to Sphinx floats (single precision)
     *
     * 32-bit, IEEE 754 single precision gives from 6 to 9 significant decimal digits precision.
     * If a decimal string with at most 6 significant decimal is converted to IEEE 754 single precision
     * and then converted back to the same number of significant decimal, then the final string should match the original;
     * and if an IEEE 754 single precision is converted to a decimal string with at least 9 significant decimal
     * and then converted back to single, then the final number MUST match the original.
     *
     * To ensure full camptibility this method converts float to a string with at most 9 significat decimal,
     * then trim trim leading zeros. Note that '123.' is a valid SphinxQL syntax for float numbers.
     *
     * @param number $value
     * @return string
     */
    public function toFloatSinglePrecision($value)
    {
        return rtrim(sprintf('%.9F', (float) $value), '0');
    }

    /**
     * @param bool $flag
     * @return SphinxQL
     */
    public function enableFloatConversion($flag = true)
    {
        $this->floatConversion = (bool) $flag;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isFloatConversionEnabled()
    {
        return $this->floatConversion;
    }

}