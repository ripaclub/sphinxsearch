<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\TestAsset;

use SphinxSearch\Db\Adapter\Platform\SphinxQL;

class TrustedSphinxQL extends SphinxQL {

    public function quoteValue($value)
    {
        return $this->quoteTrustedValue($value);
    }

} 