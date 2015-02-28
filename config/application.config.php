<?php

return [
    'modules' => [
        'Application',
        'Admin',
        // Your modules here
        'DoctrineModule',
        'DoctrineORMModule'
    ],

    'module_listener_options' => [
        'module_paths' => [
            './module',
            './vendor',
        ],

        'config_glob_paths' => [
            'config/autoload/{,*.}{global,local}.php',
        ],
    ],
];
