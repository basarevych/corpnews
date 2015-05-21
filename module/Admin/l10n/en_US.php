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
    'Data' => 'Data',
    'Data forms' => 'Data forms',
    'Import/Export' => 'Import/Export',
    'System log' => 'System log',
    'Settings' => 'Settings',
    'Email sender' => 'Email sender',

    // IndexController
    'Introduction' => 'Introduction',
    'CORPNEWS_INTRO' => 'The main entity of CorpNews is <strong>mail campaign</strong>.'
        . ' Each campaign has one or more email letter <strong>template</strong>,'
        . ' which will be sent to the <strong>client</strong> during the campaign.'
        . '</p></p>'
        . 'Combine your clients into <strong>groups</strong> (each client can be part of any number of groups)'
        . ' and select which groups will receive the letter.'
        . '</p></p>'
        . 'You can assign several <strong>tags</strong> to your campaign'
        . ' and your clients will be able to (un)subscribe to these tags.'
        . '</p></p>'
        . 'Note that there is no Email editor in CorpNews.'
        . ' Instead you use your favourite email client to create the template and then send it to the system mailbox'
        . ' where you can select it and create the campaign.'
        . '</p><p>'
        . 'The letter can include special statements to make the template dynamic.'
        . ' For example, you can write in the template "Hello, {{ first_name }}!" and this text will be replaced with'
        . ' "Hello, John!" (if the client name is John).'
        . '</p></p>'
        . 'Another feature of CorpNews is data forms.'
        . ' For example, <strong>Profile</strong> data form allows the client to edit their own profile'
        . ' and <strong>Subscription</strong> data form in order to cancel subscription to your news letter.',
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
    'Empty group' => 'Empty group',
    'Delete group' => 'Create group',
    'Number of clients' => 'Number of clients',
    'Empty' => 'Empty',
    'CONFIRM_EMPTY_GROUP' => 'Remove all clients from selected group(s)?',
    'CONFIRM_DELETE_GROUP' => 'Delete selected group(s)?',
    'CANNOT_EDIT_SYSTEM_GROUP' => 'System groups can not be edited',
    'CANNOT_DELETE_SYSTEM_GROUP' => 'System groups can not be deleted',

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
    'CONFIRM_DELETE_CLIENT' => 'Delete selected client(s)?',

    // CampaignController
    'Status filter' => 'Status filter',
    'STATUS_CREATED' => 'Created',
    'STATUS_TESTED' => 'Tested',
    'STATUS_QUEUED' => 'Queueud',
    'STATUS_STARTED' => 'Sending mail',
    'STATUS_PAUSED' => 'On pause',
    'STATUS_FINISHED' => 'Finished',
    'STATUS_ARCHIVED' => 'Archived',
    'Apply filter' => 'Apply filter',
    'Templates' => 'Templates',
    'Edit campaign' => 'Edit campaign',
    'Launch campaign' => 'Launch campaign',
    'Test campaign' => 'Test campaign',
    'Pause campaign' => 'Pause campaign',
    'Archive campaign' => 'Archive campaign',
    'Delete campaign' => 'Delete campaign',
    'Pause' => 'Pause',
    'Archive' => 'Archive',
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
    'Campaign statistics' => 'Campaign statistics',
    'Parameter' => 'Parameter',
    'Value' => 'Value',
    'Total number of clients' => 'Total number of clients',
    'Number of clients received letter' => 'Number of clients received letter',
    'Form opened' => 'Form opened',
    'Form saved' => 'Form saved',
    'Start campaign' => 'Start campaign',
    'CAMPAIGN_DEADLINE_HELP' => 'Campaign will be auto-archived after this date'
        . ' and all the links sent to the client will stop working',
    'CAMPAIGN_NO_TESTERS' => 'No testers found.'
        . '<br><br>'
        . 'Please add clients to "Testers" group',
    'CAMPAIGN_NO_GROUPS' => 'This campaign has no user groups associated',
    'CAMPAIGN_TEST_FORMS' => 'Edit selected user\'s data forms',
    'CONFIRM_START_CAMPAIGN' => 'Launch selected campaign and start sending mail?',
    'CONFIRM_CONTINUE_CAMPAIGN' => 'Continue sending mail for selected campaign?',
    'ALERT_CAMPAIGN_NOT_TESTED' => 'Campaign has not been tested!',
    'CAMPAIGN_ALREADY_LAUNCHED' => 'Campaign already launched',
    'CONFIRM_PAUSE_CAMPAIGN' => 'Pause selected campaign(s)?',
    'CONFIRM_ARCHIVE_CAMPAIGN' => 'Archive selected campaign(s)?'
        . '<br><br>Data form links received by clients will stop working.',
    'CONFIRM_DELETE_CAMPAIGN' => 'Delete selected campaign(s)?',

    // TagController
    'Description' => 'Description',
    'Create tag' => 'Create tag',
    'Delete tag' => 'Delete tag',
    'TAG_DESCRIPTION_HELP' => 'This is what client will see when unsubscribing',
    'CONFIRM_DELETE_TAG' => 'Delete selected tag(s)?',

    // DocumentController
    'Selected data form' => 'Selected data form',
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
    'Execute' => 'Execute',
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
    'Download' => 'Download',
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
    'STATUS_SENT' => 'Sent',
    'STATUS_SKIPPED' => 'Skipped',
    'STATUS_FAILED' => 'Failed',
    'Secret key' => 'Secret key',
    'Error' => 'Error',
    'When created' => 'When created',
    'When processed' => 'When processed',
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

    // ImportExportController
    'Presets' => 'Presets',
    'File format' => 'File format',
    'Excel format' => 'Excel format',
    'CSV format' => 'CSV format',
    'Field separator' => 'Field separator',
    'Comma' => 'Comma',
    'Semicolon' => 'Semicolon',
    'Tab' => 'Tab',
    'Line endings' => 'Line endings',
    'Windows' => 'Windows',
    'Unix' => 'Unix',
    'IMPORT_EXPORT_MINIMUM' => 'Minimum',
    'IMPORT_EXPORT_FULL_NAME' => 'Full name',
    'IMPORT_EXPORT_MAXIMUM' => 'Maximum',
    'Available fields' => 'Available fields',
    'Reorder columns' => 'Reorder columns',
    'Actions' => 'Actions',
    'Import' => 'Import',
    'Export' => 'Export',
    'Import data' => 'Import data',
    'IMPORT_EXPORT_NO_FIELDS' => 'No fields selected',
    'IMPORT_EXPORT_NO_EMAIL' => 'Import action requires email field to be set',
    'Encoding' => 'Encoding',
    'Import preview' => 'Import preview',
    'Accept results' => 'Accept results',
    'Cancel import' => 'Cancel import',
    '(No groups)' => '(No groups)',
    'Export data' => 'Export data',
    'Data will be imported into the following groups' => 'Data will be imported into the following groups',

    // Tours
    'Parser' => 'Parser',
    'TOUR_PARSER_INTRO' => '<p>You can use these expressions in your email template</p>',
    'TOUR_MAILBOX_INTRO' => '<p>Start working with CorpNews by sending newsletter template as an email to the application mailbox</p>',
    'TOUR_MAILBOX_TEMPLATE' => '<p>When template is received select it in the table and click this button in order to create new mail campaign</p>',
    'Outgoing' => 'Outgoing',
    'TOUR_OUTGOING_INTRO' => '<p>All email sent by the system is in this table.</p>'
        . '<p>Some letters listed here are only planned to be sent (for new mail campaigns)</p>',
    'TOUR_GROUPS_INTRO' => '<p>You can combine your clients into groups</p>'
        . '<p>One client can be listed in any number of groups</p>',
    'TOUR_CLIENTS_EDIT_CLIENT' => '<p>Click any client\'s email to edit the client'
        . '<p><p>Or click a form in "Filled forms" to open the form of the client in admin mode</p>',
    'Tags' => 'Tags',
    'TOUR_TAGS_INTRO' => '<p>Email campaign could be assigned a number of tags.</p>'
        . '<p>The client can open their Subscription form and choose which tags (themes) they want to receive.</p>'
        . '<p>By default all clients are subscribed to the tags you create</p>',
    'Campaigns' => 'Campaigns',
    'TOUR_CAMPAIGNS_INTRO' => '<p>You can control your email campaigns here.</p>'
        . '<p>Please, do not forget to test your campaigns before launching the mass mailing</p>',
    'TOUR_IMPORT_EXPORT_INTRO' => '<p>This page allows you to create export and parse import files for client\'s data forms<p>',
];
