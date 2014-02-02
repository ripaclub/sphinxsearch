<?php
/**
 * User: leodido
 * Date: 02/02/14
 * Time: 2.20
 */

namespace SphinxSearchTest\Db\Adapter\Driver\Pdo\TestAsset;

class CtorlessPdo extends \Pdo
{
    protected $mockStatement;

    public function __construct($mockStatement)
    {
        $this->mockStatement = $mockStatement;
    }

    public function prepare($sql, $options = null)
    {
        return $this->mockStatement;
    }

}