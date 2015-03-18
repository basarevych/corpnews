<?php

return [
    'controllers' => [
        'invokables' => [
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Auth' => 'Admin\Controller\AuthController',
            'Admin\Controller\Group' => 'Admin\Controller\GroupController',
            'Admin\Controller\Client' => 'Admin\Controller\ClientController',
            'Admin\Controller\Campaign' => 'Admin\Controller\CampaignController',
            'Admin\Controller\Document' => 'Admin\Controller\DocumentController',
            'Admin\Controller\Parser' => 'Admin\Controller\ParserController',
            'Admin\Controller\Mailbox' => 'Admin\Controller\MailboxController',
            'Admin\Controller\Letter' => 'Admin\Controller\LetterController',
            'Admin\Controller\Outgoing' => 'Admin\Controller\OutgoingController',
            'Admin\Controller\Setting' => 'Admin\Controller\SettingController',
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
            'admin' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/admin[/:controller[/:action]]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Admin\Controller',
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
];
