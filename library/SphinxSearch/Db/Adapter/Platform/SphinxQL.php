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
     * Quote value
     *
     * @param  string $value
     * @return string
     */
    public function quoteValue($value)
    {
        switch (true) {
            case is_null($value):
                return 'NULL';
            case is_int($value):
                return (int) $value;
            case is_float($value):
                return sprintf('%F', $value);
            default:
        }

        return parent::quoteValue($value);
    }

    /**
     * Quote Trusted Value
     *
     * The ability to quote values without notices
     *
     * @param $value
     * @return mixed
     */
    public function quoteTrustedValue($value)
    {
         switch (true) {
            case is_null($value):
                return 'NULL';
            case is_int($value):
                return (int) $value;
            case is_float($value):
                return sprintf('%F', $value);
            default:
        }


        return parent::quoteTrustedValue($value);
    }


}