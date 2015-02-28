<?php

chdir(dirname(__DIR__));

include __DIR__ . '/../init_autoloader.php';

$loader->add('ApplicationTest', 'module/Application/test');
$loader->add('AdminTest', 'module/Admin/test');
