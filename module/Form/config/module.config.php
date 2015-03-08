<?php

return [
    'controllers' => [
        'invokables' => [
            'Form\Controller\Profile' => 'Form\Controller\ProfileController',
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
            'form' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/form[/:controller[/:action]]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Form\Controller',
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
            'form_entity' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => [ __DIR__ . '/../src/Form/Entity' ],
            ],
            'orm_default' => [
                'drivers' => [
                   'Form\Entity' => 'form_entity'
                ]
            ],
            'form_document' => [
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => [ __DIR__ . '/../src/Form/Document' ],
            ],
            'odm_default' => [
                'drivers' => [
                    'Form\Document' => 'form_document'
                ]
            ]
        ]
    ],
];
