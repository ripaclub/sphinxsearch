<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests\IntegrationTest;


class MysqliIntegrationTest extends AbstractIntegrationTest
{
    protected $config = array(
        'driver'    => 'Mysqli',
        'database'  => 'dummy',
        'hostname'  => '127.0.0.1',
        'port'      => 9306,
        'charset'   => 'UTF8'
    );
}




