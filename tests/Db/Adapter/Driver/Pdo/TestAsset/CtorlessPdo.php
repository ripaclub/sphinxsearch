<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Adapter\Driver\Pdo\TestAsset;

/**
 * Class CtorlessPdo
 */
class CtorlessPdo extends \Pdo
{
    protected $mockStatement;

    /**
     * Ctor
     * @param $mockStatement
     */
    public function __construct($mockStatement)
    {
        $this->mockStatement = $mockStatement;
    }

    /**
     * @param string $sql
     * @param null $options
     * @return \PDOStatement
     */
    public function prepare($sql, $options = null)
    {
        return $this->mockStatement;
    }
}
