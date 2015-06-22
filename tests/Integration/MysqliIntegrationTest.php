<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\IntegrationTest;

/**
 * @group integration
 */
class MysqliIntegrationTest extends AbstractIntegrationTest
{
    protected $config = [
        'driver'    => 'Mysqli',
        'hostname'  => '127.0.0.1',
        'port'      => 9306,
        'charset'   => 'UTF8'
    ];

    /**
     * {@inheritdoc}
     */
    public function getSphinxVersion()
    {
        return $this->getResource()->server_info;
    }
}
