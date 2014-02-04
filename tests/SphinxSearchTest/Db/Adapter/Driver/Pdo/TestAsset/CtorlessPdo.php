<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Adapter\Driver\Pdo\TestAsset;

class CtorlessPdo extends \Pdo
{
    protected $mockStatement;

    public function __construct($mockStatement)
    {
        $this->mockStatement = $mockStatement;
    }

    public function prepare($sql, $options = null)
    {
        return $this->mockStatement;
    }

}