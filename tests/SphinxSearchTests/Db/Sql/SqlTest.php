<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests\Db\Sql;

use SphinxSearch\Db\Sql\Sql;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

class SqlTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceManager;

    /** @var AdapterInterface */
    protected $adapter = null;

    /**
     * @var Sql
     */
    protected $sql = null;

    protected function setUp()
    {
        $this->serviceManager = new ServiceManager(new ServiceManagerConfig(array(
            'factories' => array(
                'SphinxSearch\Db\Adapter\Adapter' => 'SphinxSearch\Db\Adapter\AdapterServiceFactory'
            )
        )));
        $this->serviceManager->setService('Config', array(
                'sphinxql' => array(
                    'driver' => 'pdo_mysql',
                ),
        ));
        $this->adapter = $this->serviceManager->get('SphinxSearch\Db\Adapter\Adapter');
        $this->sql = new Sql($this->adapter);
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::select
     * @testdox Select table
     */
    public function testSelect()
    {
        $table = 'foo';
        $select = $this->sql->select();
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Select', $select);
        $this->assertSame($table, $select->getRawState('table'));

        $this->setExpectedException(
            'SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "' . $table . '" provided at construction time.'
        );
        $this->sql->select('bar');
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::replace
     * @testdox Replace values into table
     */
    public function testReplace()
    {
        $table = 'foo';
        $replace = $this->sql->replace();
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Replace', $replace);
        $this->assertSame($table, $replace->getRawState('table'));

        $this->setExpectedException(
            'SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "' . $table . '" provided at construction time.');
        $this->sql->insert('bar');
    }

    /**
     * @covers SphinxSearch\Db\Sql\Sql::update
     * @testdox Update table
     */
    public function testUpdate()
    {
        $table = 'foo';
        $update = $this->sql->update();
        $this->assertInstanceOf('SphinxSearch\Db\Sql\Update', $update);
        $this->assertSame($table, $update->getRawState('table'));

        $this->setExpectedException(
            'SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            'This Sql object is intended to work with only the table "' . $table . '" provided at construction time.');
        $this->sql->update('bar');
    }

}
 