<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests;

use SphinxSearch\Db\Adapter\AdapterServiceFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;
use SphinxSearch\Search;
use SphinxSearchTests\Db\Sql\SelectTest;
class IntegrationTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @var Search
     */
    protected $search = null;

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceManager;

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    private $adapter;

    public function setUp()
    {
        $this->serviceManager = new ServiceManager(new Config(array(
            'factories' => array(
                'SphinxSearch\Db\Adapter\Adapter' => 'SphinxSearch\Db\Adapter\AdapterServiceFactory'
            ),
            'aliases' => array(
                'sphinxql' => 'SphinxSearch\Db\Adapter\Adapter'
            )
        )));
        $this->serviceManager->setService('Config', array(
            'sphinxql' => array(
                'driver'         => 'Pdo',
                'dsn'            => 'mysql:dbname=dummy;host=127.0.0.1;port=9306;',
                'driver_options' => array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                ),
            ),
        ));

        $this->adapter = $this->serviceManager->get('sphinxql');

        $this->search = new Search($this->adapter);
    }

    public function testConnection()
    {
        $this->assertInstanceOf('\Zend\Db\Adapter\Adapter', $this->adapter);

        $connection = $this->adapter->getDriver()->getConnection();

        $connection->connect();

        $this->assertTrue($connection->isConnected());
    }

    public function testSearchQueries()
    {
        $selectTest = new SelectTest();

        $data = $selectTest->providerData();

        echo PHP_EOL . 'Testing SphinxQL queries ...' . PHP_EOL;

        foreach ($data as $namedParam) {
            // $select    $sqlPrep    $params     $sqlStr    $internalTests // use named param
            list($select, $sqlPrep, $params, $sqlStr, $internalTests) = $namedParam;

            if (!$select->getRawState('table')) {
                $select = clone $select;
                $select->from('foo');
            }

            // Expr in group by NOT SUPPORTED
            if ($sqlPrep == 'SELECT * FROM `foo` GROUP BY DAY(`c1`)') {
                continue;
            }

            // Buggy
            if (strpos($sqlPrep, 'HAVING')) {
                continue;
            }

            // Not fully supported
            if (strpos($sqlPrep, 'IS NULL') || strpos($sqlPrep, 'ORDER BY isnull(`name`)')) {
                continue;
            }

            //mixing order col and expr not fully supported
            if (strpos($sqlPrep, 'DESC, RAND()')) {
                continue;
            }

            echo $sqlPrep . PHP_EOL;
            $this->search->searchWith($select);
        }

    }

}
