<?php

namespace RipaClubSphinxSearch\Db\Sql;

use Zend\Db\Sql\Insert;

class Replace extends Insert {

    /**
     * @var array Specification array
     */
    protected $specifications = array(
        self::SPECIFICATION_INSERT => 'REPLACE INTO %1$s (%2$s) VALUES (%3$s)'
    );

}