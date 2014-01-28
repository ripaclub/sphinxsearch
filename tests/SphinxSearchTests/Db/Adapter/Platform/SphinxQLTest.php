<?php
/**
 * User: leodido
 * Date: 28/01/14
 * Time: 15.59
 */

namespace SphinxSearchTests\Db\Adapter\Platform;

use SphinxSearch\Db\Adapter\Platform\SphinxQL;

class SphinxQLTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var SphinxQL
     */
    protected $platform;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->platform = new SphinxQL;
    }

    /**
     * @covers SphinxSearch\Db\Adapter\Platform\SphinxQL::getName
     */
    public function testGetName()
    {
        $this->assertEquals('SphinxQL', $this->platform->getName());
    }

}
 