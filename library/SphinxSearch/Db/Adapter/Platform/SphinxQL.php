<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Adapter\Platform;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Platform\Mysql;
use Zend\Db\Adapter\Driver\DriverInterface;

class SphinxQL extends Mysql implements PlatformInterface
{

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
            return (int) $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
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
            return (int) $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        } elseif (is_null($value)) {
            return 'NULL'; // Not supported by SphinxQL, but included for consistency with prepared statement behavior
        }

        return parent::quoteTrustedValue($value);
    }


}