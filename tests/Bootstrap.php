<?php
/**
 * ZF2 Sphinx Search
 *
 * @link        https://github.com/ripaclub/zf2-sphinxsearch
 * @copyright   Copyright (c) 2014, Leonardo Di Donato <leodidonato at gmail dot com>, Leonardo Grasso <me at leonardograsso dot com>
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace SphinxSearchTests;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

class Bootstrap {

    protected static $serviceManager;

    public static function init()
    {
        // Load the user-defined test configuration file, if it exists
        // Otherwise load
        if (is_readable(__DIR__ . '/test.config.php')) {
            $testConfig = include __DIR__ . '/test.config.php';
        } else {
            $testConfig = include __DIR__ . '/test.config.php.dist';
        }

        $zfModulePaths = array();
        if (($path = static::findParentPath('vendor'))) {
            $zfModulePaths[] = $path;
        }
        if ($path = static::findParentPath('module')) {
            $zfModulePaths[] = $path;
        }
        if ($path = static::findParentPath('src')) {
            $zfModulePaths[] = $path;
        }

        $zfModulePaths = array_unique($zfModulePaths);

        $zfModulePaths  = implode(PATH_SEPARATOR, $zfModulePaths) . PATH_SEPARATOR;
        $zfModulePaths .= getenv('ZF2_MODULES_TEST_PATHS') ? :
            (defined('ZF2_MODULES_TEST_PATHS') ? ZF2_MODULES_TEST_PATHS : '');

        static::initAutoloader();

        // use ModuleManager to load dependencies
        $baseConfig = array(
            'modules' => array(),
            'module_listener_options' => array(
                'module_paths' => explode(PATH_SEPARATOR, $zfModulePaths),
            ),
        );

        $config = ArrayUtils::merge($baseConfig, $testConfig);

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Config', array_merge($serviceManager->get('Config'), $config));
        $serviceManager->setAllowOverride(false);
        static::$serviceManager = $serviceManager;

        /**
         * Start output buffering, if enabled
         */
        if (defined('TESTS_ZEND_OB_ENABLED') && constant('TESTS_ZEND_OB_ENABLED')) {
            ob_start();
        }
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (is_readable($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        }

        $zf2Path = getenv('ZF2_PATH') ?: (defined('ZF2_PATH') ? ZF2_PATH : (is_dir($vendorPath . '/zendframework/zendframework/library') ? $vendorPath . '/zendframework/zendframework/library' : false));

        if (!$zf2Path) {
            throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
        }

        if (isset($loader)) {
            $loader->add('Zend', $zf2Path . '/Zend');
        } else {
            include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
            AutoloaderFactory::factory(array(
                    'Zend\Loader\StandardAutoloader' => array(
                        'autoregister_zf' => true,
                        'namespaces' => array(
                            __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                        ),
                    ),
                ));
        }
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) return false;
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }

}

Bootstrap::init();

