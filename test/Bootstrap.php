<?php

chdir(dirname(__DIR__));

include __DIR__ . '/../init_autoloader.php';

$loader->add('Application', 'module/Application/src');
$loader->add('ApplicationTest', 'module/Application/test');
$loader->add('Admin', 'module/Admin/src');
$loader->add('AdminTest', 'module/Admin/test');
$loader->add('DataForm', 'module/DataForm/src');
$loader->add('DataFormTest', 'module/DataForm/test');

error_reporting(0);
