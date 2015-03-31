<?php

return [
    // Admin layout
    'Mail' => 'Mail',
    'Message parser' => 'Message parser',
    'Mailbox' => 'Mailbox',
    'Outgoing messages' => 'Outgoing messages',
    'Campaign' => 'Campaign',
    'Client groups' => 'Client groups',
    'Clients' => 'Clients',
    'Campaign tags' => 'Campaign tags',
    'Mail campaigns' => 'Mail campaigns',
    'Data forms' => 'Data forms',
    'System log' => 'System log',
    'Settings' => 'Settings',
    'Email sender' => 'Email sender',

    // IndexController
    'MESSAGE_PARSER_HELP' => 'This page lists all the parser functions with their descriptions.'
        . ' Please, read this page before writing mail campaign templates',
    'MAILBOX_HELP' => 'This is an interface to the IMAP mailbox used by CorpNews',
    'OUTGOING_HELP' => 'A table with the outgoing email messages. Contains both sent messages and messages that are only scheduled to be sent',
    'GROUPS_HELP' => 'When you start a mail campaign you set which client groups will receive the letter. This page defines such groups.',
    'CLIENTS_HELP' => 'Mail campaign target is the client. This pages allows you to manage clients and to combine clients into groups.',
    'CAMPAIGNS_HELP' => 'Mail campaign management page. Allows you to edit/start/stop/view statistics of mail campaigns.',
    'DATA_FORMS_HELP' => 'Client data is stored in data forms. This page provides access to the clients data.',
    'SYSTEM_LOG_HELP' => 'Important events are written to the system log.',
    'SETTINGS_MAILBOX_HELP' => 'Set time after which old mail will be automatically deleted to keep mailbox size small',
    'Important log messages' => 'Important log messages',
    'No messages' => 'No messages',

    // AuthController
    'Restricted area' => 'Restricted area',
    'Login' => 'Login',
    'Password' => 'Password',
    'Sign in' => 'Sign in',
    'Invalid login or password' => 'Invalid login or password',

    // GroupController
    'Create group' => 'Create group',
    'Edit group' => 'Edit group',
    'Delete group' => 'Create group',
    'Number of clients' => 'Number of clients',
    'CONFIRM_DELETE_GROUP' => 'Delete selected group(s)?',
    'CANNOT_DELETE_SYSTEM_GROUPS' => 'NOTE: System groups can not be deleted',

    // ClientController
    'Filled forms' => 'Filled forms',
    'Table actions' => 'Table actions',
    'Create client' => 'Create client',
    'Edit client' => 'Edit client',
    'Delete client' => 'Delete client',
    'Email address' => 'Email address',
    'Errors' => 'Errors',
    'Email bounced' => 'Email bounced',
    'Yes' => 'Yes',
    'No' => 'No',
    'Groups' => 'Groups',
    'CANNOT_EDIT_SYSTEM_GROUP' => 'Can not edit system group',
    'CONFIRM_DELETE_CLIENT' => 'Delete selected client(s)?',

    // CampaignController
    'Status filter' => 'Status filter',
    'STATUS_CREATED' => 'Created',
    'STATUS_TESTED' => 'Tested',
    'STATUS_QUEUED' => 'Queueud',
    'STATUS_STARTED' => 'Sending mail',
    'STATUS_PAUSED' => 'On pause',
    'STATUS_FINISHED' => 'Finished',
    'Apply filter' => 'Apply filter',
    'Templates' => 'Templates',
    'Edit campaign' => 'Edit campaign',
    'Launch campaign' => 'Launch campaign',
    'Test campaign' => 'Test campaign',
    'Delete campaign' => 'Delete campaign',
    'When deadline' => 'When deadline',
    'When created' => 'When created',
    'When started' => 'When started',
    'When finished' => 'When finished',
    'Status' => 'Status',
    'Percent' => 'Percent',
    'Tester' => 'Tester',
    'Send to' => 'Send to',
    'Send test letter' => 'Send test letter',
    'Launch' => 'Launch',
    'Letter has been sent' => 'Letter has been sent',
    'Variable substitution failed' => 'Variable substitution failed',
    'Campaign test failed' => 'Campaign test failed',
    'Start campaign' => 'Start campaign',
    'CAMPAIGN_NO_TESTERS' => 'No testers found.'
        . '<br><br>'
        . 'Please add clients to "Testers" group',
    'CAMPAIGN_NO_GROUPS' => 'This campaign has no user groups associated',
    'CAMPAIGN_TEST_FORMS' => 'Edit selected user\'s data forms:',
    'CONFIRM_START_CAMPAIGN' => 'Launch selected campaign?',
    'ALERT_CAMPAIGN_NOT_TESTED' => 'Campaign has not been tested!',
    'CAMPAIGN_ALREADY_LAUNCHED' => 'Campaign already launched',
    'CONFIRM_DELETE_CAMPAIGN' => 'Delete selected campaign(s)?',

    // TagController
    'Description' => 'Description',
    'Create tag' => 'Create tag',
    'Delete tag' => 'Delete tag',
    'TAG_DESCRIPTION_HELP' => 'This is what client will see when unsubscribing',
    'CONFIRM_DELETE_TAG' => 'Delete selected tag(s)?',

    // DocumentController
    'Available data forms' => 'Available data forms',
    'When updated' => 'When updated',
    'First name' => 'First name',
    'Middle name' => 'Middle name',
    'Last name' => 'Last name',
    'Gender' => 'Gender',
    'male' => 'Male',
    'female' => 'Female',
    'Company' => 'Company',
    'Position' => 'Position',
    'TOUR_DATA_FORMS_ADMIN_ACCESS' => '<p>Click any client\'s email to open their form in admin mode</p>',
    'TOUR_DATA_FORMS_SWITCH' => '<p>You can switch current data form here</p>',

    // MailboxController
    'System email address' => 'System email address',
    'Incoming' => 'Incoming',
    'Replies' => 'Replies',
    'Bounces' => 'Bounces',
    'Delete letter' => 'Delete letter',
    'Reanalyze letter' => 'Reanalyze letter',
    'UID' => 'UID',
    'Date' => 'Date',
    'From' => 'From',
    'Subject' => 'Subject',
    'Cancel' => 'Cancel',
    '(No subject)' => '(No subject)',
    'Create campaign' => 'Create campaign',
    'CAN_NOT_CREATE_CAMPAIGN' => 'The letter(s) contains structure or syntax errors. Can not create campaign',
    'CONFIRM_CREATE_CAMPAIGN' => 'Create mail campaign with selected letter(s)?',
    'CONFIRM_DELETE_LETTER' => 'Delete selected letter(s)?',
    'CONFIRM_REANALYZE_LETTER' => 'Reset status of selected letter(s)?<br><br>Reset letters will soon reappear in one of the mailbox\'es folder again',

    // LetterController
    'Use as template' => 'Use as template',
    'Close' => 'Close',
    'Loading...' => 'Loading...',
    'HTML' => 'HTML',
    'Text' => 'Text',
    'Attachments' => 'Attachments',
    'Analysis log' => 'Analysis log',
    'Source' => 'Source',
    'Preview' => 'Preview',
    'Name' => 'Name',
    'Type' => 'Type',
    'Size' => 'Size',
    'No preview available' => 'No preview available',
    'Letter check status' => 'Letter check status',
    'Success' => 'Success',
    'Analysis error' => 'Analysis error',
    'Syntax error' => 'Syntax error',

    // ParserController
    'Message parser functions' => 'Message parser functions',
    'PARSER_SYNTAX_TITLE' => 'Parser syntax',
    'PARSER_SYNTAX_BODY' => '<p>General parser synatax is:'
        . '<pre>{{ function_name }}</pre>'
        . 'Or'
        . '<pre>{{ function_name | arg1 | ... | argN }}</pre>'
        . 'The parser will run <em>function_name</em> with the arguments and replace {{ ... }} with its output.'
        . '</p><p>'
        . '<pre>Hello, {{ first_name | Dear friend }}</pre>'
        . 'Here we are calling function <strong>first_name</strong> with the argument "Dear friend".'
        . 'This particular function prints first name of the client we are writing to. The argument is the string to print if the name is not known.'
        . '</p><p>'
        . 'The code above will be replaced with <strong>Hello, John</strong> if the first name is "John" and <strong>Hello, Dear friend</strong> if the first name is not set.'
        . '</p>',
    'PARSER_FIRST_NAME_DESCR' => '<pre>{{ first_name | Default string }}</pre>'
        . 'Prints first name of the client or "Default string" (optional) if it is not known',
    'PARSER_MIDDLE_NAME_DESCR' => '<pre>{{ middle_name | Default string }}</pre>'
        . 'Prints middle name of the client or "Default sring" (optional) if it is not known',
    'PARSER_LAST_NAME_DESCR' => '<pre>{{ last_name | Default string }}</pre>'
        . 'Prints last name of the client or "Default string" (optional) if it is not known',
    'PARSER_SHORT_NAME_DESCR' => '<pre>{{ short_full_name | Default string }}</pre>'
        . 'Prints first and middle names concatenated if they are known or "Default string" (optional)',
    'PARSER_LONG_NAME_DESCR' => '<pre>{{ long_full_name | Default string }}</pre>'
        . 'Prints first, middle and last names concatenated if they are known or "Default string" (optional)',
    'PARSER_GENDER_DESCR' => '<pre>{{ gender | Male string | Female string | Default string }}</pre>'
        . 'Prints "Male string" (required argument) if the gender of the client is male or "Female string" (required argument) if it is female. Prints "Default string" (otional) if gender is not set',
    'PARSER_COMPANY_DESCR' => '<pre>{{ company | Default string }}</pre>'
        . 'Prints company name or "Default string" (optional) if it is not known',
    'PARSER_POSITION_DESCR' => '<pre>{{ position | Default string }}</pre>'
        . 'Prints client\'s position or "Default string" (optional) if it is not known',
    'PARSER_DATA_FORM_LINK_DESCR' => '<pre>{{ data_form_link | form_name | Link text }}</pre>'
        . 'Prints link (&lt;a&gt;) to the <strong>form_name</strong> data form for the client. Both arguments are required.',
    'AVAILABLE_FORMS_TITLE' => 'Data forms',
    'AVAILABLE_FORMS_BODY' => 'List of data forms available (to use as an argument to <strong>data_form_link</strong> function):',

    // OutgoingController
    'Secret key' => 'Secret key',
    'Error' => 'Error',
    'When created' => 'When created',
    'When sent' => 'When sent',
    'From address' => 'From address',
    'To address' => 'To address',
    'Outgoing filter' => 'Outgoing filter',

    // SettingController
    'Save' => 'Save',
    'Mailbox settings' => 'Mailbox settings',
    'Autodelete' => 'Autodelete',
    'Days' => 'Days',
    'Email sender settings' => 'Email sender settings',
    'Send interval' => 'Send interval',
    'Seconds' => 'Seconds',

    // SyslogController
    'Log level filter' => 'Log level filter',
    'LEVEL_INFO' => 'LEVEL_INFO',
    'LEVEL_ERROR' => 'LEVEL_ERROR',
    'LEVEL_CRITICAL' => 'LEVEL_CRITICAL',
    'When happened' => 'When happened',
    'Source name' => 'Source name',
    'Source ID' => 'Source ID',
    'Level' => 'Level',
    'Clear log' => 'Clear log',
    'CONFIRM_CLEAR_SYSLOG' => 'Delete all entries of system log?',

    // Tours
    'Parser' => 'Parser',
    'TOUR_PARSER_INTRO' => '<p>Email templates can include dynamic statements which will be replaced with the appropriate text</p>',
    'TOUR_PARSER_MAIL' => '<p>Start working with CorpNews by sending newsletter template as an email to the application mailbox</p>',
    'TOUR_MAILBOX_INCOMING' => '<p><strong>Incoming</strong> is the folder new mail will go to.</p>',
    'TOUR_MAILBOX_REPLIES' => '<p><strong>Replies</strong> contains client replies to the campaign letters</p>',
    'TOUR_MAILBOX_BOUNCES' => '<p><strong>Bounces</strong> is the delivery failure notifications folder</p>',
    'TOUR_MAILBOX_TEMPLATE' => '<p>When template is received select it in the table and click this button in order to create new mail campaign</p>',
    'Outgoing' => 'Outgoing',
    'TOUR_OUTGOING_INTRO' => '<p>All email sent by the system is in this table.</p>'
        . '<p>Some letters here are only planned to be sent (for new mail campaigns)</p>',
    'TOUR_GROUPS_INTRO' => '<p>Email campaign sends letters to a number of client groups.</p>'
        . '<p>You can define these groups here</p>',
    'TOUR_CLIENTS_EDIT_CLIENT' => '<p>Click any client\'s email to edit the client'
        . '<p><p>Or click a form in "Filled forms" to open the form of the client in admin mode</p>',
    'Tags' => 'Tags',
    'TOUR_TAGS_INTRO' => '<p>Email campaign could be assigned a number of tags.</p>'
        . '<p>The client can open their Subscription form and choose which tags (themes) they want to receive.</p>'
        . '<p>By default all clients are subscribed to the tags you create</p>',
    'Campaigns' => 'Campaigns',
    'TOUR_CAMPAIGNS_INTRO' => '<p>You can control your email campaigns here.</p>'
        . '<p>Please, do not forget to test your campaigns before launching the mass mailing</p>',
];
