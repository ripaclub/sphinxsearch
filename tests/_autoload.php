<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

/**
 * Setup autoloading
 */
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new RuntimeException('This component has dependencies that are unmet.

Either build a vendor/autoloader.php that will load this components dependencies ...

OR

Install composer (http://getcomposer.org), and run the following
command in the root of this project:

    php /path/to/composer.phar install

After that, you should be able to run tests.');
} else {
    include_once __DIR__ . '/../vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    if (0 !== strpos($class, 'SphinxSearchTest\\')) {
        return false;
    }
    $normalized = str_replace('SphinxSearchTest\\', '', $class);
    $filename   = __DIR__ . '/SphinxSearch/' . str_replace(array('\\', '_'), '/', $normalized) . '.php';
    if (!file_exists($filename)) {
        return false;
    }

    return include_once $filename;
});