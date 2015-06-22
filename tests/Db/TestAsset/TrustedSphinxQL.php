<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\TestAsset;

use SphinxSearch\Db\Adapter\Platform\SphinxQL;

/**
 * Class TrustedSphinxQL
 */
class TrustedSphinxQL extends SphinxQL
{
    /**
     * @param string $value
     * @return string
     */
    public function quoteValue($value)
    {
        return $this->quoteTrustedValue($value);
    }
}
