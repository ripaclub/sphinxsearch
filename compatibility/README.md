Compatibility for ZF from 2.1.x to ZF 2.3.x
=============

Due to Zend\Db\Sql BCs introduced in ZF 2.4.x series, this folder contains legacy version of some classes in order to mantain compatibility with old Zend Framework versions.

This folder should be not autoloaded because lastest version of this package will polyfill classes loading the correct ones.

Polyfill may be removed in newer versions.