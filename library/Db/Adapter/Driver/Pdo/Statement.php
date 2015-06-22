<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015
 *              Leo Di Donato <leodidonato at gmail dot com>,
 *              Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace SphinxSearch\Db\Adapter\Driver\Pdo;

use Zend\Db\Adapter\Driver\Pdo\Statement as ZendPdoStatement;
use Zend\Db\Adapter\ParameterContainer;

/**
 * Class Statement
 */
class Statement extends ZendPdoStatement
{
    /**
     * Bind parameters from container
     */
    protected function bindParametersFromContainer()
    {
        if ($this->parametersBound) {
            return;
        }
        $parameters = $this->parameterContainer->getNamedArray();
        foreach ($parameters as $name => &$value) {
            // if param has no errata, PDO will detect the right type
            $type = null;

            if ($this->parameterContainer->offsetHasErrata($name)) {
                switch ($this->parameterContainer->offsetGetErrata($name)) {
                    case ParameterContainer::TYPE_INTEGER:
                        $type = \PDO::PARAM_INT;
                        break;
                    case ParameterContainer::TYPE_NULL:
                        $type = \PDO::PARAM_NULL;
                        break;
                    case ParameterContainer::TYPE_DOUBLE:
                        $value = (float)$value;
                        break;
                    case ParameterContainer::TYPE_LOB:
                        $type = \PDO::PARAM_LOB;
                        break;
                }
            }

            // Parameter is named or positional, value is reference
            $parameter = is_int($name) ? ($name + 1) : $name;
            $this->resource->bindParam($parameter, $value, $type);
        }
    }
}
