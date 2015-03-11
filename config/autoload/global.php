<?php

return [
    'service_manager' => [
        'invokables' => [
            'Session'           => 'Application\Service\Session',
            'Mail'              => 'Application\Service\Mail',
            'ErrorStrategy'     => 'Application\Service\ErrorStrategy',
            'ImapClient'        => 'Application\Service\ImapClient',
            'DataFormManager'   => 'Application\Service\DataFormManager',
        ],

        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],

        'aliases' => [
            'translator' => 'MvcTranslator',
        ],
    ],

    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',     // We can return JsonModel instead of ViewModel
        ],
    ],

    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'types' => [
                    'utcdatetime' => 'Application\Doctrine\UtcDateTime'
                ]
            ],
        ],
    ],

    'connection' => [
        'orm_default' => [
            'doctrine_type_mappings' => [
                'utcdatetime' => 'utcdatetime'
            ],
        ]
    ],

    'corpnews' => [
        'data_forms' => [
            'profile' => [
                'title'     => 'Profile',
                'url'       => '/data-form/profile',
                'document'  => 'DataForm\Document\Profile',
                'form'      => 'DataForm\Form\Profile',
                'table'     => 'DataForm\Table\Profile',
            ],
        ],
    ],
];
