<?php

return [
    'service_manager' => [
        'invokables' => [
            'Session'           => 'Application\Service\Session',
            'Mail'              => 'Application\Service\Mail',
            'ErrorStrategy'     => 'Application\Service\ErrorStrategy',
            'ImapClient'        => 'Application\Service\ImapClient',
            'DataFormManager'   => 'Application\Service\DataFormManager',
            'Parser'            => 'Application\Service\Parser',
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
        'parser' => [
            'variables' => [
                'first_name' => [
                    'descr'     => 'PARSER_FIRST_NAME_DESCR',
                    'class'     => 'DataForm\Variable\FirstName',
                ],
                'middle_name' => [
                    'descr'     => 'PARSER_MIDDLE_NAME_DESCR',
                    'class'     => 'DataForm\Variable\MiddleName',
                ],
                'last_name' => [
                    'descr'     => 'PARSER_LAST_NAME_DESCR',
                    'class'     => 'DataForm\Variable\LastName',
                ],
                'short_name' => [
                    'descr'     => 'PARSER_SHORT_NAME_DESCR',
                    'class'     => 'DataForm\Variable\ShortName',
                ],
                'full_name' => [
                    'descr'     => 'PARSER_FULL_NAME_DESCR',
                    'class'     => 'DataForm\Variable\FullName',
                ],
                'gender' => [
                    'descr'     => 'PARSER_GENDER_DESCR',
                    'class'     => 'DataForm\Variable\Gender',
                ],
                'company' => [
                    'descr'     => 'PARSER_COMPANY_DESCR',
                    'class'     => 'DataForm\Variable\Company',
                ],
                'position' => [
                    'descr'     => 'PARSER_POSITION_DESCR',
                    'class'     => 'DataForm\Variable\Position',
                ],
            ],
        ],
    ],
];
