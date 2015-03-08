<?php

return [
    'controllers' => [
        'invokables' => [
            'DataForm\Controller\Profile' => 'DataForm\Controller\ProfileController',
        ],
    ],

    'view_manager' => [
        'doctype' => 'HTML5',
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'router' => [
        'routes' => [
            'data-form' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/data-form[/:controller[/:action]]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'DataForm\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],

    'translator' => [
        'locales' => [ 'en_US', 'ru_RU' ],
        'default' => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../l10n',
                'pattern'  => '%s.php',
            ],
        ],
    ],

    'doctrine' => [
        'driver' => [
            'data_form_entity' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => [ __DIR__ . '/../src/DataForm/Entity' ],
            ],
            'orm_default' => [
                'drivers' => [
                   'DataForm\Entity' => 'data_form_entity'
                ]
            ],
            'data_form_document' => [
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => [ __DIR__ . '/../src/DataForm/Document' ],
            ],
            'odm_default' => [
                'drivers' => [
                    'DataForm\Document' => 'data_form_document'
                ]
            ]
        ]
    ],
];
