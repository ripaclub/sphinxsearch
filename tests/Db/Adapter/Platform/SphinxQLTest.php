<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015 Leo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearchTest\Db\Adapter\Platform;

use SphinxSearch\Db\Adapter\Platform\SphinxQL;

/**
 * Class SphinxQLTest
 */
class SphinxQLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SphinxQL
     */
    protected $platform;

    /**
     * @covers SphinxSearch\Db\Adapter\Platform\SphinxQL::getName
     */
    public function testGetName()
    {
        $this->assertEquals('SphinxQL', $this->platform->getName());
    }

    public function testQuoteValue()
    {
        $this->assertEquals(3, $this->platform->quoteValue(3));
        $this->assertSame('NULL', $this->platform->quoteValue(null));
        $this->assertEquals(1.11, $this->platform->quoteValue(1.11));
    }

    /**
     * @covers SphinxSearch\Db\Adapter\Platform\SphinxQL::quoteValue
     * @testdox Trigger E_USER_NOTICE when quoted a not supported value type
     */
    public function testQuoteWrongValue()
    {
        $this->setExpectedException('\PHPUnit_Framework_Error_Notice');
        $this->platform->quoteValue('ciao');
    }

    public function testQuoteTrustedValue()
    {
        $this->assertEquals(3, $this->platform->quoteTrustedValue(3));
        $this->assertSame('NULL', $this->platform->quoteTrustedValue(null));
        $this->assertEquals(1.11, $this->platform->quoteTrustedValue(1.11));
        $this->assertSame('\'ciao\'', $this->platform->quoteTrustedValue('ciao'));
    }

    public function testFloatConversion()
    {
        //assume default enabled
        $this->assertTrue($this->platform->isFloatConversionEnabled());

        $this->assertInstanceOf(
            '\SphinxSearch\Db\Adapter\Platform\SphinxQL',
            $this->platform->enableFloatConversion(false)
        );
        $this->assertFalse($this->platform->isFloatConversionEnabled());

        $this->platform->enableFloatConversion(true);
        $this->assertTrue($this->platform->isFloatConversionEnabled());

        //test default param value in method
        $this->platform->enableFloatConversion();
        $this->assertTrue($this->platform->isFloatConversionEnabled());

        $singlePrecisionPi = '3.141592654';
        $doublePrecisionPi = '3.1415926535898';

        $this->assertSame($singlePrecisionPi, $this->platform->toFloatSinglePrecision(pi()));

        $this->platform->enableFloatConversion(false);
        $this->assertSame($doublePrecisionPi, $this->platform->quoteTrustedValue(pi()));

        $this->platform->enableFloatConversion(true);
        $this->assertSame($singlePrecisionPi, $this->platform->quoteTrustedValue(pi()));
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->platform = new SphinxQL;
    }
}
