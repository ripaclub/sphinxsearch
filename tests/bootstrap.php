<?php
/**
 * Matryoshka
 *
 * @link        https://github.com/ripaclub/matryoshka
 * @copyright   Copyright (c) 2014, Ripa Club <ripaclub@gmail.com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */


chdir(__DIR__);
setlocale(LC_NUMERIC, 'C');

if (!file_exists('../vendor/autoload.php')) {
        throw new \RuntimeException('vendor/autoload.php not found. Run a composer install.');
}

$autoloader = include '../vendor/autoload.php';
$autoloader->add('SphinxSearchTest', __DIR__);
