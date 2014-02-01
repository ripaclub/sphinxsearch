<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests\IntegrationTest;

class PDOIntegrationTest extends AbstractIntegrationTest
{
    protected $config = array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=dummy;host=127.0.0.1;port=9306;',
        'driver_options' => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        )
    );
}




