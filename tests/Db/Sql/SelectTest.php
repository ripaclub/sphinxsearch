<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Sql;

use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use SphinxSearch\Db\Sql\Exception\InvalidArgumentException;
use SphinxSearch\Db\Sql\Platform\ExpressionDecorator;
use SphinxSearch\Db\Sql\Select;
use SphinxSearchTest\Db\TestAsset\TrustedSphinxQL;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\TableIdentifier;
use Zend\Version\Version;

/**
 * Class SelectTest
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
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
     * @covers SphinxSearch\Db\Sql\Select::from
     * @testdox Method from() returns Select object (is chainable)
     */
    public function testFrom()
    {
        $select = new Select;
        $return = $select->from('baz', 'ignore schema');
        $this->assertSame($select, $return);
        $this->assertEquals('baz', $this->readAttribute($select, 'table'));


        $tableIdentifier = new TableIdentifier('foo', 'ignore schema');
        $select->from($tableIdentifier);
        $this->assertEquals('foo', $this->readAttribute($select, 'table'));

        return $return;
    }

    /**
     * @testdox Method getRawState() returns information populated via from()
     * @covers  SphinxSearch\Db\Sql\Select::getRawState
     * @depends testFrom
     */
    public function testGetRawStateViaFrom(Select $select)
    {
        $this->assertEquals('foo', $select->getRawState('table'));
    }

    /**
     * @covers SphinxSearch\Db\Sql\Select::columns
     * @testdox Method columns() returns Select object (is chainable)
     */
    public function testColumns()
    {
        $select = new Select;
        $return = $select->columns(['foo', 'bar']);
        $this->assertSame($select, $return);

        return $select;
    }

    /**
     * @testdox Method getRawState() returns information populated via columns()
     * @covers  SphinxSearch\Db\Sql\Select::getRawState
     * @depends testColumns
     */
    public function testGetRawStateViaColumns(Select $select)
    {
        $this->assertEquals(['foo', 'bar'], $select->getRawState('columns'));
    }

    /**
     * @testdox Method limit()
     * @covers SphinxSearch\Db\Sql\Select::limit
     */
    public function testLimit()
    {
        $select = new Select;
        $this->assertSame($select, $select->limit(5));
        return $select;
    }

    /**
     * @testdox Method getRawState() returns information populated via limit()
     * @covers  SphinxSearch\Db\Sql\Select::getRawState
     * @depends testLimit
     */
    public function testGetRawStateViaLimit(Select $select)
    {
        $this->assertEquals(5, $select->getRawState($select::LIMIT));
    }

    /**
     * @testdox Method test offset()
     * @covers SphinxSearch\Db\Sql\Select::offset
     */
    public function testOffset()
    {
        $select = new Select;
        $this->assertSame($select, $select->offset(10));
        return $select;
    }

    /**
     * @testdox Method getRawState() returns information populated via offset()
     * @covers  SphinxSearch\Db\Sql\Select::getRawState
     * @depends testOffset
     */
    public function testGetRawStateViaOffset(Select $select)
    {
        $this->assertEquals(10, $select->getRawState($select::OFFSET));
    }


    /**
     * @testdox Method group() returns same Select object (is chainable)
     * @covers SphinxSearch\Db\Sql\Select::group
     */
    public function testGroup()
    {
        $select = new Select;
        $return = $select->group(['col1', 'col2']);
        $this->assertSame($select, $return);

        return $return;
    }

    /**
     * @testdox Method getRawState() returns information populated via group()
     * @covers  SphinxSearch\Db\Sql\Select::getRawState
     * @depends testGroup
     */
    public function testGetRawStateViaGroup(Select $select)
    {
        $this->assertEquals(
            ['col1', 'col2'],
            $select->getRawState('group')
        );
    }

    /**
     * @testdox Method withinGroupOrder() returns same Select object (is chainable)
     * @covers SphinxSearch\Db\Sql\Select::withinGroupOrder
     */
    public function testWithinGroupOrder()
    {
        $select = new Select;
        $return = $select->withinGroupOrder(['col1', 'col2']);
        $this->assertSame($select, $return);

        return $return;
    }

    /**
     * @testdox Method getRawState() returns information populated via withinGroupOrder()
     * @covers  SphinxSearch\Db\Sql\Select::getRawState
     * @depends testWithinGroupOrder
     */
    public function testGetRawStateViaWithinGroupOrder(Select $select)
    {
        $this->assertEquals(
            ['col1', 'col2'],
            $select->getRawState('withingrouporder')
        );

        return $select;
    }

    /**
     * @testdox Method withinGroupOrder() with string parameter
     * @covers  SphinxSearch\Db\Sql\Select::withinGroupOrder
     * @depends testGetRawStateViaWithinGroupOrder
     */
    public function testWithinGroupOrderParamAsString(Select $select)
    {
        $expression = new Expression('colE');
        $select->withinGroupOrder('col3');
        $select->withinGroupOrder('col4, col5');
        $select->withinGroupOrder($expression);
        $select->withinGroupOrder(['alias' => 'col6']);
        $this->assertEquals(
            ['col1', 'col2', 'col3', 'col4', 'col5', $expression, 'alias' => 'col6'],
            $select->getRawState('withingrouporder')
        );
    }

    /**
     * @testdox Method having() returns same Select object (is chainable)
     * @covers SphinxSearch\Db\Sql\Select::having
     */
    public function testHaving()
    {
        $select = new Select;
        $return = $select->having(['x = ?' => 5]);
        $this->assertSame($select, $return);

        return $return;
    }

    /**
     * @testdox Method getRawState() returns information populated via having()
     * @covers  SphinxSearch\Db\Sql\Select::getRawState
     * @depends testHaving
     */
    public function testGetRawStateViaHaving(Select $select)
    {
        $this->assertInstanceOf('\Zend\Db\Sql\Having', $select->getRawState('having'));
    }

    /**
     * @testdox Method option() returns same Select object (is chainable)
     * @covers SphinxSearch\Db\Sql\Select::option
     */
    public function testOption()
    {
        $select = new Select;
        $return = $select->option(['opt_name' => 'opt_value']);
        $return = $select->option(['opt_name2' => 'opt_value2']);
        $this->assertSame($select, $return);

        return $return;
    }

    /**
     * @testdox Method getRawState() returns information populated via option()
     * @covers  SphinxSearch\Db\Sql\Select::getRawState
     * @depends testOption
     */
    public function testGetRawOption(Select $select)
    {
        $this->assertEquals(
            ['opt_name' => 'opt_value', 'opt_name2' => 'opt_value2'],
            $select->getRawState('option')
        );

        return $select;
    }

    /**
     * @testdox Method option() with OPTIONS_SET flag
     * @covers  SphinxSearch\Db\Sql\Select::option
     * @covers  SphinxSearch\Db\Sql\Select::getRawState
     * @depends testGetRawOption
     */
    public function testOptionSet(Select $select)
    {
        $select->option(['opt_name3' => 'opt_value3'], $select::OPTIONS_SET);
        $this->assertEquals(
            ['opt_name3' => 'opt_value3'],
            $select->getRawState('option')
        );
    }

    /**
     * @testdox Method option() launch exception with null values
     * @expectedException InvalidArgumentException
     * @depends testGetRawOption
     */
    public function testNullOptionValues(Select $select)
    {
        $select->option([]);
    }

    /**
     * @testdox Method option() launch exception when value keys are not strings
     * @expectedException InvalidArgumentException
     * @depends testGetRawOption
     */
    public function testNotStringOptionValueKeys(Select $select)
    {
        $select->option([1 => 'opt_values4']);
    }

    /**
     * @testdox Method reset() resets internal state of Select object, based on input
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
        $select->columns(['foo']);
        $this->assertEquals(['foo'], $select->getRawState(Select::COLUMNS));
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
        $select->group(['foo']);
        $this->assertEquals(['foo'], $select->getRawState(Select::GROUP));
        $select->reset(Select::GROUP);
        $this->assertEmpty($select->getRawState(Select::GROUP));

        // within group order by
        $select->withinGroupOrder(['foo']);
        $this->assertEquals(['foo'], $select->getRawState(Select::WITHINGROUPORDER));
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
        $this->assertEquals(['foo asc'], $select->getRawState(Select::ORDER));
        $select->reset(Select::ORDER);
        $this->assertEmpty($select->getRawState(Select::ORDER));

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

        // option
        $select->option(['ranker' => 'bm25']);
        $this->assertEquals(['ranker' => 'bm25'], $select->getRawState(Select::OPTION));
        $select->reset(Select::OPTION);
        $this->assertEmpty($select->getRawState(Select::OPTION));
    }


    /**
     * @testdox Method prepareStatement() will produce expected sql and parameters based on a variety of provided arguments [uses data provider]
     * @covers       SphinxSearch\Db\Sql\Select::prepareStatement
     * @dataProvider providerData
     */
    public function testPrepareStatement(
        Select $select,
        $expectedSqlString,
        $expectedParameters,
        $unused1,
        $unused2,
        $useNamedParameters = false
    ) {
        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('formatParameterName')->will(
            $this->returnCallback(
                function ($name) use ($useNamedParameters) {
                    return (($useNamedParameters) ? ':' . $name : '?');
                }
            )
        );
        $mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, [$mockDriver, new TrustedSphinxQL()]);

        $parameterContainer = new ParameterContainer();

        $mockStatement = $this->getMock('\Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('getParameterContainer')->will(
            $this->returnValue($parameterContainer)
        );
        $mockStatement->expects($this->any())->method('setSql')->with($this->equalTo($expectedSqlString));

        $select->prepareStatement($mockAdapter, $mockStatement);

        if ($expectedParameters) {
            $this->assertEquals($expectedParameters, $parameterContainer->getNamedArray());
        }
    }


    /**
     * @testdox Method getSqlString() will produce expected sql and parameters based on a variety of provided arguments [uses data provider]
     * @covers       SphinxSearch\Db\Sql\Select::getSqlString
     * @dataProvider providerData
     */
    public function testGetSqlString(Select $select, $unused, $unused2, $expectedSqlString)
    {
        $this->assertEquals($expectedSqlString, $select->getSqlString(new TrustedSphinxQL()));
    }

    /**
     * @testdox Method processExpression() methods will return proper array when internally called, part of extension API
     * @covers SphinxSearch\Db\Sql\Select::processExpression
     */
    public function testProcessExpression()
    {
        $select = new Select();
        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $parameterContainer = new ParameterContainer();

        $selectReflect = new \ReflectionObject($select);
        $mr = $selectReflect->getMethod('processExpression');
        $mr->setAccessible(true);

        //Test with an Expression
        $return = $mr->invokeArgs(
            $select,
            [new Expression('?', 10.1), new TrustedSphinxQL(), $mockDriver, $parameterContainer]
        );

        $this->assertInternalType('string', $return);

        //Test with an ExpressionDecorator
        $return2 = $mr->invokeArgs(
            $select,
            [
                new ExpressionDecorator(new Expression('?', 10.1), new SphinxQL()),
                new TrustedSphinxQL(),
                $mockDriver,
                $parameterContainer
            ]
        );

        $this->assertInternalType('string', $return2);

        $this->assertSame($return, $return2);
        $this->assertEquals('10.1', $return);
    }

    /**
     * @testdox Method process*() methods will return proper array when internally called, part of extension API
     * @dataProvider providerData
     * @covers       SphinxSearch\Db\Sql\Select::processSelect
     * @covers       SphinxSearch\Db\Sql\Select::processWithinGroupOrder
     * @covers       SphinxSearch\Db\Sql\Select::processLimitOffset
     * @covers       SphinxSearch\Db\Sql\Select::processOption
     * @covers       SphinxSearch\Db\Sql\Select::processExpression
     */
    public function testProcessMethods(Select $select, $unused, $unused2, $unused3, $internalTests)
    {
        if (!$internalTests) {
            return;
        }

        $mockDriver = $this->getMock('\Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $parameterContainer = new ParameterContainer();

        $selectReflect = new \ReflectionObject($select);

        foreach ($internalTests as $method => $expected) {
            $mr = $selectReflect->getMethod($method);
            $mr->setAccessible(true);
            $return = $mr->invokeArgs($select, [new TrustedSphinxQL, $mockDriver, $parameterContainer]);
            $this->assertEquals($expected, $return);
        }
    }

    public function testProcessLimitOffsetWithoutOptionalParams()
    {
        $offset = 1;
        $limit = 10;
        $select = new Select;
        $select->from('foo');
        $select->offset($offset);
        $select->limit($limit);

        $selectReflect = new \ReflectionObject($select);
        $method = $selectReflect->getMethod('processLimitOffset');
        $method->setAccessible(true);
        $return = $method->invokeArgs($select, [new TrustedSphinxQL]);
        $this->assertEquals([$offset, $limit], $return);

        $method->setAccessible(false);
    }

    public function testProcessOptionWithoutOptionalParams()
    {
        $opts = ['foo' => 'bar'];
        $select = new Select;
        $select->from('foo');
        $select->option($opts);
        $platform = new TrustedSphinxQL;

        $selectReflect = new \ReflectionObject($select);
        $method = $selectReflect->getMethod('processOption');
        $method->setAccessible(true);
        $return = $method->invokeArgs($select, [$platform]);
        $this->assertEquals(
            [
                [
                    [
                        $platform->quoteIdentifier('foo'),
                        $platform->quoteValue('bar')
                    ]
                ]
            ],
            $return
        );

        $method->setAccessible(false);
    }

    public function providerData()
    {
        // basic table
        $select0 = new Select;
        $select0->from('foo');
        $sqlPrep0 = // same
        $sqlStr0 = 'SELECT * FROM `foo`';
        $internalTests0 = [
            'processSelect' => [[['*']], '`foo`']
        ];

        // table as TableIdentifier
        $select1 = new Select;
        $select1->from(new TableIdentifier('foo'));
        $sqlPrep1 = // same
        $sqlStr1 = 'SELECT * FROM `foo`';
        $internalTests1 = [
            'processSelect' => [[['*']], '`foo`']
        ];

        // table list
        $select2 = new Select;
        $select2->from(['foo', 'bar']);
        $sqlPrep2 = // same
        $sqlStr2 = 'SELECT * FROM `foo`, `bar`';
        $internalTests2 = [
            'processSelect' => [[['*']], '`foo`, `bar`']
        ];

        // table list (comma separated)
        $select3 = new Select;
        $select3->from('foo, baz');
        $sqlPrep3 = // same
        $sqlStr3 = 'SELECT * FROM `foo`, `baz`';
        $internalTests3 = [
            'processSelect' => [[['*']], '`foo`, `baz`']
        ];

        // columns
        $select4 = new Select;
        $select4->from('foo')->columns(['bar', 'baz']);
        $sqlPrep4 = // same
        $sqlStr4 = 'SELECT `bar`, `baz` FROM `foo`';
        $internalTests4 = [
            'processSelect' => [[['`bar`'], ['`baz`']], '`foo`']
        ];

        // columns with AS associative array
        $select5 = new Select;
        $select5->from('foo')->columns(['bar' => 'baz']);
        $sqlPrep5 = // same
        $sqlStr5 = 'SELECT `baz` AS `bar` FROM `foo`';
        $internalTests5 = [
            'processSelect' => [[['`baz`', '`bar`']], '`foo`']
        ];

        // columns with AS associative array mixed
        $select6 = new Select;
        $select6->from('foo')->columns(['bar' => 'baz', 'bam']);
        $sqlPrep6 = // same
        $sqlStr6 = 'SELECT `baz` AS `bar`, `bam` FROM `foo`';
        $internalTests6 = [
            'processSelect' => [[['`baz`', '`bar`'], ['`bam`']], '`foo`']
        ];

        // columns where value is Expression, with AS
        $select7 = new Select;
        $select7->from('foo')->columns(['bar' => new Expression('COUNT(*)')]);
        $sqlPrep7 = // same
        $sqlStr7 = 'SELECT COUNT(*) AS `bar` FROM `foo`';
        $internalTests7 = [
            'processSelect' => [[['COUNT(*)', '`bar`']], '`foo`']
        ];

        // columns where value is Expression
        $select8 = new Select;
        $select8->from('foo')->columns([new Expression('COUNT(*) AS bar')]);
        $sqlPrep8 = // same
        $sqlStr8 = 'SELECT COUNT(*) AS bar FROM `foo`';
        $internalTests8 = [
            'processSelect' => [[['COUNT(*) AS bar']], '`foo`']
        ];

        // columns where value is Expression with parameters
        $select9 = new Select;
        $select9->from('foo')->columns(
            [
                new Expression(
                    'EXIST(?, 5) AS ?',
                    ['baz', 'bar'],
                    [Expression::TYPE_VALUE, Expression::TYPE_IDENTIFIER]
                )
            ]
        );
        $sqlPrep9 = 'SELECT EXIST(?, 5) AS `bar` FROM `foo`';
        $sqlStr9 = 'SELECT EXIST(\'baz\', 5) AS `bar` FROM `foo`';
        $params9 = ['column1' => 'baz'];
        $internalTests9 = [
            'processSelect' => [[['EXIST(?, 5) AS `bar`']], '`foo`']
        ];

        // select without from and expression in column without alias
        $select10 = new Select;
        $select10->columns([new Expression('1+1')]);
        $sqlPrep10 = // same
        $sqlStr10 = 'SELECT 1+1 AS `Expression1`';
        $internalTests10 = [
            'processSelect' => [[['1+1', '`Expression1`']]]
        ];

        // test join (silent ignore)
        $select11 = new Select;
        $select11->from('foo')->join('zac', 'm = n', ['bar', 'baz']);
        $sqlPrep11 = // same
        $sqlStr11 = 'SELECT * FROM `foo`';
        $internalTests11 = [
            'processSelect' => [[['*']], '`foo`'],
        ];

        // FIXME
        // NOTE: assuming float as literal [default behaviour]
        $platform = new TrustedSphinxQL(); //use platform to ensure same float point precision
        $ten = $platform->quoteValue(10.0);
        $select12 = new Select;
        $select12->from('foo')->columns(['f1', 'test' => new Expression('?', 10.0)]);
        $sqlPrep12 = // same
        $sqlStr12 = 'SELECT `f1`, ' . $ten . ' AS `test` FROM `foo`';
        $internalTests12 = [
            'processSelect' => [[['`f1`'], [$ten, '`test`']], '`foo`'],
        ];

//         // join with alternate type
//         $select12 = new Select;
//         $select12->from('foo')->join('zac', 'm = n', ['bar', 'baz'], Select::JOIN_OUTER);
//         $sqlPrep12 = // same
//         $sqlStr12 = 'SELECT `foo`.*, `zac`.`bar` AS `bar`, `zac`.`baz` AS `baz` FROM `foo` OUTER JOIN `zac` ON `m` = `n`';
//         $internalTests12 = array(
//             'processSelect' => array(array(array('`foo`.*'), array('`zac`.`bar`', '`bar`'), array('`zac`.`baz`', '`baz`')), '`foo`'),
//             'processJoins'   => array(array(array('OUTER', '`zac`', '`m` = `n`')))
//         );

//         // join with column aliases
//         $select13 = new Select;
//         $select13->from('foo')->join('zac', 'm = n', array('BAR' => 'bar', 'BAZ' => 'baz'));
//         $sqlPrep13 = // same
//         $sqlStr13 = 'SELECT `foo`.*, `zac`.`bar` AS `BAR`, `zac`.`baz` AS `BAZ` FROM `foo` INNER JOIN `zac` ON `m` = `n`';
//         $internalTests13 = array(
//             'processSelect' => array(array(array('`foo`.*'), array('`zac`.`bar`', '`BAR`'), array('`zac`.`baz`', '`BAZ`')), '`foo`'),
//             'processJoins'   => array(array(array('INNER', '`zac`', '`m` = `n`')))
//         );

//         // join with table aliases
//         $select14 = new Select;
//         $select14->from('foo')->join(array('b' => 'bar'), 'b.foo_id = foo.foo_id');
//         $sqlPrep14 = // same
//         $sqlStr14 = 'SELECT `foo`.*, `b`.* FROM `foo` INNER JOIN `bar` AS `b` ON `b`.`foo_id` = `foo`.`foo_id`';
//         $internalTests14 = array(
//             'processSelect' => array(array(array('`foo`.*'), array('`b`.*')), '`foo`'),
//             'processJoins' => array(array(array('INNER', '`bar` AS `b`', '`b`.`foo_id` = `foo`.`foo_id`')))
//         );

        // where (simple string)
        $select15 = new Select;
        $select15->from('foo')->where('c1 = 5');
        $sqlPrep15 = // same
        $sqlStr15 = 'SELECT * FROM `foo` WHERE c1 = 5';
        $internalTests15 = [
            'processSelect' => [[['*']], '`foo`'],
            'processWhere' => ['c1 = 5']
        ];

        // where (returning parameters)
        $select16 = new Select;
        $select16->from('bar')->where(['x = ?' => 5]);
        $sqlPrep16 = 'SELECT * FROM `bar` WHERE x = ?';
        $sqlStr16 = 'SELECT * FROM `bar` WHERE x = 5';
        $params16 = ['where1' => 5];
        $internalTests16 = [
            'processSelect' => [[['*']], '`bar`'],
            'processWhere' => ['x = ?']
        ];

        // group
        $select17 = new Select;
        $select17->from('foo')->group(['c1', 'c2']);
        $sqlPrep17 = // same
        $sqlStr17 = 'SELECT * FROM `foo` GROUP BY `c1`, `c2`';
        $internalTests17 = [
            'processSelect' => [[['*']], '`foo`'],
            'processGroup' => [['`c1`', '`c2`']]
        ];

        $select18 = new Select;
        $select18->from('foo')->group('c1')->group('c2');
        $sqlPrep18 = // same
        $sqlStr18 = 'SELECT * FROM `foo` GROUP BY `c1`, `c2`';
        $internalTests18 = [
            'processSelect' => [[['*']], '`foo`'],
            'processGroup' => [['`c1`', '`c2`']]
        ];

        $select19 = new Select;
        $select19->from('foo')->group(new Expression('DAY(?)', ['c1'], [Expression::TYPE_IDENTIFIER]));
        $sqlPrep19 = // same
        $sqlStr19 = 'SELECT * FROM `foo` GROUP BY DAY(`c1`)';
        $internalTests19 = [
            'processSelect' => [[['*']], '`foo`'],
            'processGroup' => [['DAY(`c1`)']]
        ];

        // having (simple string)
        $select20 = new Select;
        $select20->from('foo')->group('c1')->having('baz = 0');
        $sqlPrep20 = // same
        $sqlStr20 = 'SELECT * FROM `foo` GROUP BY `c1` HAVING baz = 0';
        $internalTests20 = [
            'processSelect' => [[['*']], '`foo`'],
            'processGroup' => [['`c1`']],
            'processHaving' => ['baz = 0']
        ];

        // having (returning parameters)
        $select21 = new Select;
        $select21->from('foo')->group('c1')->having(['baz = ?' => 5]);
        $sqlPrep21 = 'SELECT * FROM `foo` GROUP BY `c1` HAVING baz = ?';
        $sqlStr21 = 'SELECT * FROM `foo` GROUP BY `c1` HAVING baz = 5';
        $params21 = ['having1' => 5];
        $internalTests21 = [
            'processSelect' => [[['*']], '`foo`'],
            'processGroup' => [['`c1`']],
            'processHaving' => ['baz = ?']
        ];

        // order
        $select22 = new Select;
        $select22->from('foo')->order('c1');
        $sqlPrep22 = //
        $sqlStr22 = 'SELECT * FROM `foo` ORDER BY `c1` ASC';
        $internalTests22 = [
            'processSelect' => [[['*']], '`foo`'],
            'processOrder' => [[['`c1`', Select::ORDER_ASCENDING]]]
        ];

        $select23 = new Select;
        $select23->from('foo')->order(['c1', 'c2']);
        $sqlPrep23 = // same
        $sqlStr23 = 'SELECT * FROM `foo` ORDER BY `c1` ASC, `c2` ASC';
        $internalTests23 = [
            'processSelect' => [[['*']], '`foo`'],
            'processOrder' => [
                [
                    ['`c1`', Select::ORDER_ASCENDING],
                    ['`c2`', Select::ORDER_ASCENDING]
                ]
            ]
        ];

        $select24 = new Select;
        $select24->from('foo')->order(['c1' => 'DESC', 'c2' => 'Asc']); // notice partially lower case ASC
        $sqlPrep24 = // same
        $sqlStr24 = 'SELECT * FROM `foo` ORDER BY `c1` DESC, `c2` ASC';
        $internalTests24 = [
            'processSelect' => [[['*']], '`foo`'],
            'processOrder' => [
                [
                    ['`c1`', Select::ORDER_DESCENDING],
                    ['`c2`', Select::ORDER_ASCENDING]
                ]
            ]
        ];

        $select25 = new Select;
        $select25->from('foo')->order(['c1' => 'asc'])->order('c2 desc'); // notice partially lower case ASC
        $sqlPrep25 = // same
        $sqlStr25 = 'SELECT * FROM `foo` ORDER BY `c1` ASC, `c2` DESC';
        $internalTests25 = [
            'processSelect' => [[['*']], '`foo`'],
            'processOrder' => [
                [
                    ['`c1`', Select::ORDER_ASCENDING],
                    ['`c2`', Select::ORDER_DESCENDING]
                ]
            ]
        ];

        // limit
        $select26 = new Select;
        $select26->from('foo')->limit(5);
        $sqlPrep26 = 'SELECT * FROM `foo` LIMIT ?,?';
        $sqlStr26 = 'SELECT * FROM `foo` LIMIT 0,5';
        $params26 = ['limit' => 5, 'offset' => 0];
        $internalTests26 = [
            'processSelect' => [[['*']], '`foo`'],
            'processLimitOffset' => ['?', '?']
        ];

        // limit with offset
        $select27 = new Select;
        $select27->from('foo')->limit(5)->offset(10);
        $sqlPrep27 = 'SELECT * FROM `foo` LIMIT ?,?';
        $sqlStr27 = 'SELECT * FROM `foo` LIMIT 10,5';
        $params27 = ['limit' => 5, 'offset' => 10];
        $internalTests27 = [
            'processSelect' => [[['*']], '`foo`'],
            'processLimitOffset' => ['?', '?']
        ];

//         // joins with a few keywords in the on clause
//         $select28 = new Select;
//         $select28->from('foo')->join('zac', '(m = n AND c.x) BETWEEN x AND y.z OR (c.x < y.z AND c.x <= y.z AND c.x > y.z AND c.x >= y.z)');
//         $sqlPrep28 = // same
//         $sqlStr28 = 'SELECT `foo`.*, `zac`.* FROM `foo` INNER JOIN `zac` ON (`m` = `n` AND `c`.`x`) BETWEEN `x` AND `y`.`z` OR (`c`.`x` < `y`.`z` AND `c`.`x` <= `y`.`z` AND `c`.`x` > `y`.`z` AND `c`.`x` >= `y`.`z`)';
//         $internalTests28 = array(
//             'processSelect' => array(array(array('`foo`.*'), array('`zac`.*')), '`foo`'),
//             'processJoins'  => array(array(array('INNER', '`zac`', '(`m` = `n` AND `c`.`x`) BETWEEN `x` AND `y`.`z` OR (`c`.`x` < `y`.`z` AND `c`.`x` <= `y`.`z` AND `c`.`x` > `y`.`z` AND `c`.`x` >= `y`.`z`)')))
//         );

//         // order with compound name
//         $select29 = new Select;
//         $select29->from('foo')->order('c1.d2');
//         $sqlPrep29 = //
//         $sqlStr29 = 'SELECT `foo`.* FROM `foo` ORDER BY `c1`.`d2` ASC';
//         $internalTests29 = array(
//             'processSelect' => array(array(array('`foo`.*')), '`foo`'),
//             'processOrder'  => array(array(array('`c1`.`d2`', Select::ORDER_ASCENDING)))
//         );

//         // group with compound name
//         $select30 = new Select;
//         $select30->from('foo')->group('c1.d2');
//         $sqlPrep30 = // same
//         $sqlStr30 = 'SELECT `foo`.* FROM `foo` GROUP BY `c1`.`d2`';
//         $internalTests30 = array(
//             'processSelect' => array(array(array('`foo`.*')), '`foo`'),
//             'processGroup'  => array(array('`c1`.`d2`'))
//         );

//         // join with expression in ON part
//         $select31 = new Select;
//         $select31->from('foo')->join('zac', new Expression('(m = n AND c.x) BETWEEN x AND y.z'));
//         $sqlPrep31 = // same
//         $sqlStr31 = 'SELECT `foo`.*, `zac`.* FROM `foo` INNER JOIN `zac` ON (m = n AND c.x) BETWEEN x AND y.z';
//         $internalTests31 = array(
//             'processSelect' => array(array(array('`foo`.*'), array('`zac`.*')), '`foo`'),
//             'processJoins'   => array(array(array('INNER', '`zac`', '(m = n AND c.x) BETWEEN x AND y.z')))
//         );

        $select32subselect = new Select;
        $select32subselect->from('bar')->where(['y' => 1]);
        $select32 = new Select;
        $select32->from($select32subselect)->order('x');
        $sqlPrep32 = 'SELECT * FROM (SELECT * FROM `bar` WHERE `y` = ?) ORDER BY `x` ASC';
        $sqlStr32 = 'SELECT * FROM (SELECT * FROM `bar` WHERE `y` = 1) ORDER BY `x` ASC';
        $internalTests32 = [
            'processSelect' => [[['*']], '(SELECT * FROM `bar` WHERE `y` = ?)'],
        ];

        // not yet supported by Sphinx
        $select33 = new Select;
        $select33->from('foo')->columns(['*'])->where(
            [
                'c1' => null,
                'c2' => [1, 2, 3],
                new \Zend\Db\Sql\Predicate\IsNotNull('c3')
            ]
        );
        $sqlPrep33 = 'SELECT * FROM `foo` WHERE `c1` IS NULL AND `c2` IN (?, ?, ?) AND `c3` IS NOT NULL';
        $sqlStr33 = 'SELECT * FROM `foo` WHERE `c1` IS NULL AND `c2` IN (1, 2, 3) AND `c3` IS NOT NULL';
        $internalTests33 = [
            'processSelect' => [[['*']], '`foo`'],
            'processWhere' => ['`c1` IS NULL AND `c2` IN (?, ?, ?) AND `c3` IS NOT NULL']
        ];

        // not yet supported by Sphinx
        // @author Demian Katz
        $select34 = new Select;
        $select34->from('foo')->order(
            [
                new Expression('isnull(?) DESC', ['name'], [Expression::TYPE_IDENTIFIER]),
                'name'
            ]
        );
        $sqlPrep34 = 'SELECT * FROM `foo` ORDER BY isnull(`name`) DESC, `name` ASC';
        $sqlStr34 = 'SELECT * FROM `foo` ORDER BY isnull(`name`) DESC, `name` ASC';
        $internalTests34 = [
            'processOrder' => [[['isnull(`name`) DESC'], ['`name`', Select::ORDER_ASCENDING]]]
        ];

//         // join with Expression object in COLUMNS part (ZF2-514)
//         // @co-author Koen Pieters (kpieters)
//         $select35 = new Select;
//         $select35->from('foo')->columns(array())->join('bar', 'm = n', array('thecount' => new Expression(`COUNT(*)`)));
//         $sqlPrep35 = // same
//         $sqlStr35 = 'SELECT COUNT(*) AS `thecount` FROM `foo` INNER JOIN `bar` ON `m` = `n`';
//         $internalTests35 = array(
//             'processSelect' => array(array(array('COUNT(*)', '`thecount`')), '`foo`'),
//             'processJoins'   => array(array(array('INNER', '`bar`', '`m` = `n`')))
//         );

//         // multiple joins with expressions
//         // reported by @jdolieslager
//         $select36 = new Select;
//         $select36->from('foo')
//         ->join('tableA', new Predicate\Operator('id', '=', 1))
//         ->join('tableB', new Predicate\Operator('id', '=', 2))
//         ->join('tableC', new Predicate\PredicateSet(array(
//             new Predicate\Operator('id', '=', 3),
//             new Predicate\Operator('number', '>', 20)
//         )));
//         $sqlPrep36 = 'SELECT `foo`.*, `tableA`.*, `tableB`.*, `tableC`.* FROM `foo`'
//             . ' INNER JOIN `tableA` ON `id` = :join1part1 INNER JOIN `tableB` ON `id` = :join2part1 '
//                 . 'INNER JOIN `tableC` ON `id` = :join3part1 AND `number` > :join3part2';
//         $sqlStr36 = 'SELECT `foo`.*, `tableA`.*, `tableB`.*, `tableC`.* FROM `foo` '
//             . 'INNER JOIN `tableA` ON `id` = \'1\' INNER JOIN `tableB` ON `id` = \'2\' '
//                 . 'INNER JOIN `tableC` ON `id` = \'3\' AND `number` > \'20\'';
//         $internalTests36 = array();
//         $useNamedParams36 = true;

        /**
         * @author robertbasic
         * @link https://github.com/zendframework/zf2/pull/2714
         */
        $select37 = new Select;
        $select37->from('foo')->columns(['bar'], false);
        $sqlPrep37 = // same
        $sqlStr37 = 'SELECT `bar` FROM `foo`';
        $internalTests37 = [
            'processSelect' => [[['`bar`']], '`foo`']
        ];

//         // @link https://github.com/zendframework/zf2/issues/3294
//         // Test TableIdentifier In Joins
//         $select38 = new Select;
//         $select38->from('foo')->columns(array())->join(new TableIdentifier('bar', 'baz'), 'm = n', array('thecount' => new Expression(`COUNT(*)`)));
//         $sqlPrep38 = // same
//         $sqlStr38 = 'SELECT COUNT(*) AS `thecount` FROM `foo` INNER JOIN `baz`.`bar` ON `m` = `n`';
//         $internalTests38 = array(
//             'processSelect' => array(array(array('COUNT(*)', '`thecount`')), '`foo`'),
//             'processJoins'   => array(array(array('INNER', '`baz`.`bar`', '`m` = `n`')))
//         );

//         // subselect in join
//         $select39subselect = new Select;
//         $select39subselect->from('bar')->where->like('y', '%Foo%');
//         $select39 = new Select;
//         $select39->from('foo')->join(array('z' => $select39subselect), 'z.foo = bar.id');
//         $sqlPrep39 = 'SELECT `foo`.*, `z`.* FROM `foo` INNER JOIN (SELECT `bar`.* FROM `bar` WHERE `y` LIKE ?) AS `z` ON `z`.`foo` = `bar`.`id`';
//         $sqlStr39 = 'SELECT `foo`.*, `z`.* FROM `foo` INNER JOIN (SELECT `bar`.* FROM `bar` WHERE `y` LIKE \'%Foo%\') AS `z` ON `z`.`foo` = `bar`.`id`';
//         $internalTests39 = array(
//             'processJoins' => array(array(array('INNER', '(SELECT `bar`.* FROM `bar` WHERE `y` LIKE ?) AS `z`', '`z`.`foo` = `bar`.`id`')))
//         );

//         // @link https://github.com/zendframework/zf2/issues/3294
//         // Test TableIdentifier In Joins, with multiple joins
//         $select40 = new Select;
//         $select40->from('foo')
//         ->join(array('a' => new TableIdentifier('another_foo', 'another_schema')), 'a.x = foo.foo_column')
//         ->join('bar', 'foo.colx = bar.colx');
//         $sqlPrep40 = // same
//         $sqlStr40 = 'SELECT `foo`.*, `a`.*, `bar`.* FROM `foo`'
//             . ' INNER JOIN `another_schema`.`another_foo` AS `a` ON `a`.`x` = `foo`.`foo_column`'
//                 . ' INNER JOIN `bar` ON `foo`.`colx` = `bar`.`colx`';
//         $internalTests40 = array(
//             'processSelect' => array(array(array('`foo`.*'), array('`a`.*'), array('`bar`.*')), '`foo`'),
//             'processJoins'  => array(array(
//                 array('INNER', '`another_schema`.`another_foo` AS `a`', '`a`.`x` = `foo`.`foo_column`'),
//                 array('INNER', '`bar`', '`foo`.`colx` = `bar`.`colx`')
//             ))
//         );

        //test quantifier (silent ignore)
        $select41 = new Select;
        $select41->from('foo')->quantifier(Select::QUANTIFIER_DISTINCT);
        $sqlPrep41 = // same
        $sqlStr41 = 'SELECT * FROM `foo`';
        $internalTests41 = [
            'processSelect' => [[['*']], '`foo`'],
        ];

        $select42 = new Select;
        $select42->from('foo')->quantifier(new Expression('TOP ?', [10]));
        $sqlPrep42 = //same
        $sqlStr42 = 'SELECT * FROM `foo`';
        $internalTests42 = [
            'processSelect' => [[['*']], '`foo`'],
        ];

        //test table alias (silent ignore)
        $select43 = new Select();
        $select43->from(['x' => 'foo'])//table alias will be ignored
        ->columns(['bar'], false);
        $sqlPrep43 = //same
        $sqlStr43 = 'SELECT `bar` FROM `foo`';
        $internalTests43 = [
            'processSelect' => [[['`bar`']], '`foo`']
        ];

        //test combine (silent ignore)
        $select44 = new Select;
        $select44->from('foo');
        $select44b = new Select;
        $select44b->from('bar')->where('c = d');
        $select44->combine($select44b, Select::COMBINE_UNION, 'ALL');
        $sqlPrep44 = // same
        $sqlStr44 = 'SELECT * FROM `foo`';
        $internalTests44 = [
            'processSelect' => [[['*']], '`foo`'],
        ];

        // limit with offset
        $select45 = new Select;
        $select45->from('foo')->limit("5")->offset("10");
        $sqlPrep45 = 'SELECT * FROM `foo` LIMIT ?,?';
        $sqlStr45 = 'SELECT * FROM `foo` LIMIT 10,5';
        $params45 = ['limit' => 5, 'offset' => 10];
        $internalTests45 = [
            'processSelect' => [[['*']], '`foo`'],
            'processLimitOffset' => ['?', '?']
        ];


        // within group order
        $select46 = new Select;
        $select46->from('foo')->group('baz')->withinGroupOrder('c1');
        $sqlPrep46 = //
        $sqlStr46 = 'SELECT * FROM `foo` GROUP BY `baz` WITHIN GROUP ORDER BY `c1` ASC';
        $internalTests46 = [
            'processSelect' => [[['*']], '`foo`'],
            'processGroup' => [['`baz`']],
            'processWithinGroupOrder' => [[['`c1`', Select::ORDER_ASCENDING]]]
        ];

        $select47 = new Select;
        $select47->from('foo')->group('baz')->withinGroupOrder(['c1', 'c2']);
        $sqlPrep47 = // same
        $sqlStr47 = 'SELECT * FROM `foo` GROUP BY `baz` WITHIN GROUP ORDER BY `c1` ASC, `c2` ASC';
        $internalTests47 = [
            'processSelect' => [[['*']], '`foo`'],
            'processGroup' => [['`baz`']],
            'processWithinGroupOrder' => [
                [
                    ['`c1`', Select::ORDER_ASCENDING],
                    ['`c2`', Select::ORDER_ASCENDING]
                ]
            ]
        ];

        $select48 = new Select;
        $select48->from('foo')->group('baz')->withinGroupOrder(
            ['c1' => 'DESC', 'c2' => 'Asc']
        ); // notice partially lower case ASC
        $sqlPrep48 = // same
        $sqlStr48 = 'SELECT * FROM `foo` GROUP BY `baz` WITHIN GROUP ORDER BY `c1` DESC, `c2` ASC';
        $internalTests48 = [
            'processSelect' => [[['*']], '`foo`'],
            'processGroup' => [['`baz`']],
            'processWithinGroupOrder' => [
                [
                    ['`c1`', Select::ORDER_DESCENDING],
                    ['`c2`', Select::ORDER_ASCENDING]
                ]
            ]
        ];

        $select49 = new Select; //testing all features for code coverage (i.e. Sphinx doesn't support Expression in order yet)
        $select49->from('foo')->group('baz')->withinGroupOrder(['c1' => 'asc'])->withinGroupOrder(
            'c2 desc'
        )->withinGroupOrder(['c3', 'baz DESC'])->withinGroupOrder(new Expression('RAND()'));
        $sqlPrep49 = // same
        $sqlStr49 = 'SELECT * FROM `foo` GROUP BY `baz` WITHIN GROUP ORDER BY `c1` ASC, `c2` DESC, `c3` ASC, `baz` DESC, RAND()';
        $internalTests49 = [
            'processSelect' => [[['*']], '`foo`'],
            'processGroup' => [['`baz`']],
            'processWithinGroupOrder' => [
                [
                    ['`c1`', Select::ORDER_ASCENDING],
                    ['`c2`', Select::ORDER_DESCENDING],
                    ['`c3`', Select::ORDER_ASCENDING],
                    ['`baz`', Select::ORDER_DESCENDING],
                    ['RAND()']
                ]
            ]
        ];

        // option
        $select50 = new Select;
        $select50->from('foo')->option(
            ['ranker' => 'bm25', 'max_matches' => 500, 'field_weights' => new Expression('(title=10, body=3)')]
        );
        $sqlPrep50 = 'SELECT * FROM `foo` OPTION `ranker` = ?, `max_matches` = ?, `field_weights` = (title=10, body=3)';
        $sqlStr50 = 'SELECT * FROM `foo` OPTION `ranker` = \'bm25\', `max_matches` = 500, `field_weights` = (title=10, body=3)';
        $internalTests50 = [
            'processSelect' => [[['*']], '`foo`'],
            'processOption' => [
                [
                    ['`ranker`', '?'],
                    ['`max_matches`', '?'],
                    ['`field_weights`', '(title=10, body=3)']
                ]
            ]
        ];


        /**
         * $select = the select object
         * $sqlPrep = the sql as a result of preparation
         * $params = the param container contents result of preparation
         * $sqlStr = the sql as a result of getting a string back
         * $internalTests what the internal functions should return (safe-guarding extension)
         */

        return [
            //    $select    $sqlPrep    $params     $sqlStr    $internalTests    // use named param
            [$select0, $sqlPrep0, [], $sqlStr0, $internalTests0],
            [$select1, $sqlPrep1, [], $sqlStr1, $internalTests1],
            [$select2, $sqlPrep2, [], $sqlStr2, $internalTests2],
            [$select3, $sqlPrep3, [], $sqlStr3, $internalTests3],
            [$select4, $sqlPrep4, [], $sqlStr4, $internalTests4],
            [$select5, $sqlPrep5, [], $sqlStr5, $internalTests5],
            [$select6, $sqlPrep6, [], $sqlStr6, $internalTests6],
            [$select7, $sqlPrep7, [], $sqlStr7, $internalTests7],
            [$select8, $sqlPrep8, [], $sqlStr8, $internalTests8],
            [$select9, $sqlPrep9, $params9, $sqlStr9, $internalTests9],
            [$select10, $sqlPrep10, [], $sqlStr10, $internalTests10],
            [$select11, $sqlPrep11, [], $sqlStr11, $internalTests11],
            [$select12, $sqlPrep12, [], $sqlStr12, $internalTests12],
//             array($select13, $sqlPrep13, array(),    $sqlStr13, $internalTests13),
//             array($select14, $sqlPrep14, array(),    $sqlStr14, $internalTests14),
            [$select15, $sqlPrep15, [], $sqlStr15, $internalTests15],
            [$select16, $sqlPrep16, $params16, $sqlStr16, $internalTests16],
            [$select17, $sqlPrep17, [], $sqlStr17, $internalTests17],
            [$select18, $sqlPrep18, [], $sqlStr18, $internalTests18],
            [$select19, $sqlPrep19, [], $sqlStr19, $internalTests19],
            [$select20, $sqlPrep20, [], $sqlStr20, $internalTests20],
            [$select21, $sqlPrep21, $params21, $sqlStr21, $internalTests21],
            [$select22, $sqlPrep22, [], $sqlStr22, $internalTests22],
            [$select23, $sqlPrep23, [], $sqlStr23, $internalTests23],
            [$select24, $sqlPrep24, [], $sqlStr24, $internalTests24],
            [$select25, $sqlPrep25, [], $sqlStr25, $internalTests25],
            [$select26, $sqlPrep26, $params26, $sqlStr26, $internalTests26],
            [$select27, $sqlPrep27, $params27, $sqlStr27, $internalTests27],
//             array($select28, $sqlPrep28, array(),    $sqlStr28, $internalTests28),
//             array($select29, $sqlPrep29, array(),    $sqlStr29, $internalTests29),
//             array($select30, $sqlPrep30, array(),    $sqlStr30, $internalTests30),
//             array($select31, $sqlPrep31, array(),    $sqlStr31, $internalTests31),
            [$select32, $sqlPrep32, [], $sqlStr32, $internalTests32],
            [$select33, $sqlPrep33, [], $sqlStr33, $internalTests33],
            [$select34, $sqlPrep34, [], $sqlStr34, $internalTests34],
//             array($select35, $sqlPrep35, array(),    $sqlStr35, $internalTests35),
//             array($select36, $sqlPrep36, array(),    $sqlStr36, $internalTests36,  $useNamedParams36),
            [$select37, $sqlPrep37, [], $sqlStr37, $internalTests37],
//             array($select38, $sqlPrep38, array(),    $sqlStr38, $internalTests38),
//             array($select39, $sqlPrep39, array(),    $sqlStr39, $internalTests39),
//             array($select40, $sqlPrep40, array(),    $sqlStr40, $internalTests40),
            [$select41, $sqlPrep41, [], $sqlStr41, $internalTests41],
            [$select42, $sqlPrep42, [], $sqlStr42, $internalTests42],
            [$select43, $sqlPrep43, [], $sqlStr43, $internalTests43],
            [$select44, $sqlPrep44, [], $sqlStr44, $internalTests44],
            [$select45, $sqlPrep45, $params45, $sqlStr45, $internalTests45],
            [$select46, $sqlPrep46, [], $sqlStr46, $internalTests46],
            [$select47, $sqlPrep47, [], $sqlStr47, $internalTests47],
            [$select48, $sqlPrep48, [], $sqlStr48, $internalTests48],
            [$select49, $sqlPrep49, [], $sqlStr49, $internalTests49],
            [$select50, $sqlPrep50, [], $sqlStr50, $internalTests50],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     * @testdox Columns cannot be prefixed with the table name
     */
    public function testPrefixColumnsWithTable()
    {
        $select = new Select;
        $select->columns(['uid'], true);
    }

    /**
     * @testdox Tables have to be strings, array, TableIdentifier objects or Select objects
     */
    public function testTableType()
    {
        $select = new Select;
        $this->setExpectedException(
            '\SphinxSearch\Db\Sql\Exception\InvalidArgumentException',
            '$table must be a string, array, an instance of TableIdentifier, or an instance of Select'
        );
        $table = new \stdClass();
        $select->from($table);
    }

    /**
     * @testdox Tables defined in construction phase are read only
     */
    public function testTableReadOnly()
    {
        $select = new Select('foo');
        $this->setExpectedException(
            '\SphinxSearch\Db\Sql\Exception\InvalidArgumentException'
        );
        $select->from('foo');
    }

    /**
     * @testdox Can not reset read only table
     */
    public function testResetReadOnlyTable()
    {
        $select = new Select('foo');
        $this->setExpectedException(
            '\SphinxSearch\Db\Sql\Exception\InvalidArgumentException'
        );
        $select->reset(Select::TABLE);
    }
}
