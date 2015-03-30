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
            'Logger'            => 'Application\Service\Logger',
            'TaskDaemon'        => 'Application\Service\TaskDaemon',
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
            'subscription' => [
                'title'     => 'Subscription',
                'url'       => '/data-form/subscription',
                'document'  => 'DataForm\Document\Subscription',
                'form'      => 'DataForm\Form\Subscription',
                'table'     => 'DataForm\Table\Subscription',
            ],
        ],
        'parser' => [
            'functions' => [
                'first_name' => [
                    'descr'     => 'PARSER_FIRST_NAME_DESCR',
                    'class'     => 'DataForm\ParserFunction\FirstName',
                    'html'      => false,
                ],
                'middle_name' => [
                    'descr'     => 'PARSER_MIDDLE_NAME_DESCR',
                    'class'     => 'DataForm\ParserFunction\MiddleName',
                    'html'      => false,
                ],
                'last_name' => [
                    'descr'     => 'PARSER_LAST_NAME_DESCR',
                    'class'     => 'DataForm\ParserFunction\LastName',
                    'html'      => false,
                ],
                'short_full_name' => [
                    'descr'     => 'PARSER_SHORT_NAME_DESCR',
                    'class'     => 'DataForm\ParserFunction\ShortFullName',
                    'html'      => false,
                ],
                'long_full_name' => [
                    'descr'     => 'PARSER_LONG_NAME_DESCR',
                    'class'     => 'DataForm\ParserFunction\LongFullName',
                    'html'      => false,
                ],
                'gender' => [
                    'descr'     => 'PARSER_GENDER_DESCR',
                    'class'     => 'DataForm\ParserFunction\Gender',
                    'html'      => false,
                ],
                'company' => [
                    'descr'     => 'PARSER_COMPANY_DESCR',
                    'class'     => 'DataForm\ParserFunction\Company',
                    'html'      => false,
                ],
                'position' => [
                    'descr'     => 'PARSER_POSITION_DESCR',
                    'class'     => 'DataForm\ParserFunction\Position',
                    'html'      => false,
                ],
                'data_form_link' => [
                    'descr'     => 'PARSER_DATA_FORM_LINK_DESCR',
                    'class'     => 'DataForm\ParserFunction\DataFormLink',
                    'html'      => true,
                ],
            ],
        ],
        'task_daemon' => [
            'tasks' => [
                'check_email'           => 'Application\TaskDaemon\CheckEmail',
                'queued_campaign'       => 'Application\TaskDaemon\QueuedCampaign',
                'all_queued_campaigns'  => 'Application\TaskDaemon\AllQueuedCampaigns',
                'send_email'            => 'Application\TaskDaemon\SendEmail',
            ],
        ],
    ],
];
