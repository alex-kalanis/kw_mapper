<?php

define('AUTHOR_NAME', 'kalanis');
define('PROJECT_NAME', 'kw_mapper');
define('PROJECT_DIR', 'php-src');
require_once __DIR__ . '/_autoload.php';
require_once __DIR__ . '/CommonTestClass.php';

\kalanis\kw_mapper\Storage\Database\ConfigStorage::getInstance()->addConfig(
    \kalanis\kw_mapper\Storage\Database\Config::init()->setTarget(
        \kalanis\kw_mapper\Interfaces\IDriverSources::TYPE_PDO_MYSQL, 'devel', 'localhost', 3306, 'kwdeploy', 'testingpass', 'kw_deploy'
    ));
