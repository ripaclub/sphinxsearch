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

use Zend\Db\Sql\Sql as ZendSql;

/**
 * Class Sql
 */
class Sql extends ZendSql
{
    public function select($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'This Sql object is intended to work with only the table "%s" provided at construction time.',
                    $this->table
                )
            );
        }

        return new Select(($table) ? : $this->table);
    }

    public function insert($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'This Sql object is intended to work with only the table "%s" provided at construction time.',
                    $this->table
                )
            );
        }

        return new Insert(($table) ? : $this->table);
    }

    public function replace($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'This Sql object is intended to work with only the table "%s" provided at construction time.',
                    $this->table
                )
            );
        }

        return new Replace(($table) ? : $this->table);
    }

    public function update($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'This Sql object is intended to work with only the table "%s" provided at construction time.',
                    $this->table
                )
            );
        }

        return new Update(($table) ? : $this->table);
    }

    public function delete($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'This Sql object is intended to work with only the table "%s" provided at construction time.',
                    $this->table
                )
            );
        }

        return new Delete(($table) ? : $this->table);
    }

    public function show()
    {
        return new Show();
    }
}
