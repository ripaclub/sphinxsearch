<?php
/**
 * Sphinx Search
 *
 * @link        https://github.com/ripaclub/sphinxsearch
 * @copyright   Copyright (c) 2014-2015
 *              Leo Di Donato <leodidonato at gmail dot com>
 *              Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

chdir(__DIR__);

// Set the decimal separator to "C" (i.e. minimal "C" locale)
setlocale(LC_NUMERIC, 'C');

if (!file_exists('../vendor/autoload.php')) {
        throw new \RuntimeException('vendor/autoload.php not found. Run a composer install.');
}
