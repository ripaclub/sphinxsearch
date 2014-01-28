<?php
/**
 * User: leodido
 * Date: 28/01/14
 * Time: 17.36
 */

namespace SphinxSearchTests\Db\Sql;

use SphinxSearch\Db\Sql\Select;

class SelectTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers SphinxSearch\Db\Sql\Select::__construct
     */
    public function testConstruct()
    {
        $select = new Select('foo');
        $this->assertEquals('foo', $select->getRawState('table'));
    }

    /**
     * @testdox Test from() returns Select object (is chainable)
     * @covers SphinxSearch\Db\Sql\Select::from
     */
    public function testFrom()
    {
        $select = new Select;
        $return = $select->from('foo', 'bar');
        $this->assertSame($select, $return);

        return $return;
    }



}
 