<?php
return array(
    'service_manager' => array(
        'abstract_factories' => array(
            'SphinxSearch\Db\Adapter\AdapterAbstractServiceFactory'
        ),
        'factories' => array(
            'SphinxSearch\Db\Adapter\Adapter' => 'SphinxSearch\Db\Adapter\AdapterServiceFactory',
        ),
        'aliases' => array(
            'sphinxql' => 'SphinxSearch\Db\Adapter\Adapter'
        ),
    ),
);