<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTests\Db\Sql;

use SphinxSearch\Db\Sql\Select;

class SelectTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers SphinxSearch\Db\Sql\Select::__construct
     * @testdox Instantiation
     */
    public function testConstruct()
    {
        $select = new Select('foo');
        $this->assertEquals('foo', $select->getRawState('table'));
    }

    /**
     * @covers SphinxSearch\Db\Sql\Select::columns
     * @testdox Method columns() returns Select object (is chainable)
     */
    public function testColumns()
    {
        $select = new Select;
        $return = $select->columns(array('foo', 'bar'));
        $this->assertSame($select, $return);

        return $select;
    }

    /**
     * @covers SphinxSearch\Db\Sql\Select::from
     * @testdox Method from() returns Select object (is chainable)
     */
    public function testFrom()
    {
        $select = new Select;
        $return = $select->from('foo', 'bar');
        $this->assertSame($select, $return);

        return $return;
    }

    /**
     * @testdox Method reset() resets internal stat of Select object, based on input
     * @covers SphinxSearch\Db\Sql\Select::reset
     */
    public function testReset()
    {
        $select = new Select;

        // table
        $select->from('foo');
        $this->assertEquals('foo', $select->getRawState(Select::TABLE));
        $select->reset(Select::TABLE);
        $this->assertNull($select->getRawState(Select::TABLE));

        // columns
        $select->columns(array('foo'));
        $this->assertEquals(array('foo'), $select->getRawState(Select::COLUMNS));
        $select->reset(Select::COLUMNS);
        $this->assertEmpty($select->getRawState(Select::COLUMNS));

        // where
        $select->where('foo = bar');
        $where1 = $select->getRawState(Select::WHERE);
        $this->assertEquals(1, $where1->count());
        $select->reset(Select::WHERE);
        $where2 = $select->getRawState(Select::WHERE);
        $this->assertEquals(0, $where2->count());
        $this->assertNotSame($where1, $where2);

        // group
        $select->group(array('foo'));
        $this->assertEquals(array('foo'), $select->getRawState(Select::GROUP));
        $select->reset(Select::GROUP);
        $this->assertEmpty($select->getRawState(Select::GROUP));

        // within group order by
        $select->withinGroupOrder(array('foo'));
        $this->assertEquals(array('foo'), $select->getRawState(Select::WITHINGROUPORDER));
        $select->reset(Select::WITHINGROUPORDER);
        $this->assertEmpty($select->getRawState(Select::WITHINGROUPORDER));

        // having
        $select->having('foo = bar');
        $having1 = $select->getRawState(Select::HAVING);
        $this->assertEquals(1, $having1->count());
        $select->reset(Select::HAVING);
        $having2 = $select->getRawState(Select::HAVING);
        $this->assertEquals(0, $having2->count());
        $this->assertNotSame($having1, $having2);

        // order
        $select->order('foo asc');
        $this->assertEquals(array('foo asc'), $select->getRawState(Select::ORDER));
        $select->reset(Select::ORDER);
        $this->assertNull($select->getRawState(Select::ORDER));

        // limit
        $select->limit(5);
        $this->assertEquals(5, $select->getRawState(Select::LIMIT));
        $select->reset(Select::LIMIT);
        $this->assertNull($select->getRawState(Select::LIMIT));

        // offset
        $select->offset(10);
        $this->assertEquals(10, $select->getRawState(Select::OFFSET));
        $select->reset(Select::OFFSET);
        $this->assertNull($select->getRawState(Select::OFFSET));

        // TODO: limit offset

        // TODO: option
    }

    // TODO: test process methods

}
 