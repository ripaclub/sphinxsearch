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
namespace SphinxSearch\Db\Sql;

use Zend\Db\Sql\Insert as ZendInsert;
use Zend\Db\Sql\TableIdentifier;

/**
 * Class Insert
 */
class Insert extends ZendInsert
{

    /**
     * Crete INTO clause
     *
     * @param  string|TableIdentifier $table
     * @return Insert
     */
    public function into($table)
    {
        if ($table instanceof TableIdentifier) {
            $table = $table->getTable(); // Ignore schema because it is not supported by SphinxQL
        }

        $this->table = $table;

        return $this;
    }
}
