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
    'Mail campaigns' => 'Mail campaigns',
    'Data forms' => 'Data forms',
    'Settings' => 'Settings',
    'Mailbox settings' => 'Mailbox settings',

    // IndexController
    'MESSAGE_PARSER_HELP' => 'This page lists all the parser variables with their description.'
        . ' Please, read this page before writing mail campaign templates',
    'MAILBOX_HELP' => 'This is an interface to the IMAP mailbox used by CorpNews',
    'OUTGOING_HELP' => 'A table with the outgoing email messages. Contains both sent messages and messages that are only scheduled to be sent',
    'GROUPS_HELP' => 'When you start a mail campaign you set which client groups will receive the letter. This page defines such groups.',
    'CLIENTS_HELP' => 'Mail campaign target is the client. This pages allows you to manage clients and to combine clients into groups.',
    'CAMPAIGNS_HELP' => 'Mail campaign management page. Allows you to edit/start/stop/view statistics of mail campaigns.',
    'DATA_FORMS_HELP' => 'Client data is stored in data forms. This page provides access to the clients data.',

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
    'Email bounced' => 'Email bounced',
    'Groups' => 'Groups',
    'CANNOT_EDIT_SYSTEM_GROUP' => 'Can not edit system group',
    'CONFIRM_DELETE_CLIENT' => 'Delete selected client(s)?',
    'TOUR_CLIENTS_EDIT_CLIENT' => '<p>Click any client\'s email to edit the client'
        . '<p><p>Or click a form in "Filled forms" to open the form of the client in admin mode</p>',

    // CampaignController
    'Status filter' => 'Status filter',
    'STATUS_CREATED' => 'Created',
    'STATUS_TESTED' => 'Tested',
    'STATUS_QUEUED' => 'Queueud',
    'STATUS_STARTED' => 'Started sending',
    'STATUS_PAUSED' => 'On pause',
    'STATUS_FINISHED' => 'Finished',
    'Apply filter' => 'Apply filter',
    'View template' => 'View template',
    'Delete campaign' => 'Delete campaign',
    'Status' => 'Status',
    'When deadline' => 'When deadline',
    'When created' => 'When created',
    'When started' => 'When started',
    'When finished' => 'When finished',
    'Launch mail campaign' => 'Launch mail campaign',
    'Test' => 'Test',
    'Launch' => 'Launch',
    'Tester' => 'Tester',
    'Send to' => 'Send to',
    'Variables' => 'Variables',
    'Send test letter' => 'Send test letter',
    'Letter has been sent' => 'Letter has been sent',
    'Variable substitution failed' => 'Variable substitution failed',
    'Sending the letter failed' => 'Sending the letter failed',
    'Start campaign' => 'Start campaign',
    'CAMPAIGN_NO_TESTERS' => 'No testers found.'
        . '<br><br>'
        . 'Please add clients to "Testers" group',
    'CAMPAIGN_TEST_FORMS' => 'Mail parser variables are set to the appropriate fields of data forms'
        . '<br><br>'
        . 'Edit selected user\'s data forms:',
    'CONFIRM_DELETE_CAMPAIGN' => 'Delete selected campaign(s)?',

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
    'Incoming' => 'Incoming',
    'Replies' => 'Replies',
    'Bounces' => 'Bounces',
    'Use as template' => 'Use as template',
    'Delete letter' => 'Delete letter',
    'Reanalyze letter' => 'Reanalyze letter',
    'UID' => 'UID',
    'Date' => 'Date',
    'From' => 'From',
    'Subject' => 'Subject',
    'Close' => 'Close',
    'Cancel' => 'Cancel',
    '(No subject)' => '(No subject)',
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
    'Create campaign' => 'Create campaign',
    'CAN_NOT_CREATE_CAMPAIGN' => 'The letter(s) contains structure or syntax errors. Can not create campaign',
    'CONFIRM_CREATE_CAMPAIGN' => 'Create mail campaign with selected letter(s)?',
    'CONFIRM_DELETE_LETTER' => 'Delete selected letter(s)?',
    'CONFIRM_REANALYZE_LETTER' => 'Reset status of selected letter(s)?<br><br>Reset letters will soon reappear in one of the mailbox\'es folder again',
    'Execute' => 'Execute',

    // ParserController
    'Message parser variables' => 'Message parser variables',
    'PARSER_SYNTAX_TITLE' => 'Parser syntax',
    'PARSER_SYNTAX_BODY' => '<p>General parser synatax is:'
        . '<pre>{{ any_php_code }}</pre>'
        . 'The parser will run <em>any_php_code</em> and replace it with the code output.'
        . '</p><p>'
        . 'Some examples:'
        . '<pre>Hello, {{ echo $short_full_name }}</pre>'
        . 'Will be replaced with <strong>"Hello, John Doe"</strong> if the first name is "John" and last name is "Doe".'
        . 'If the variable is not set or does not exist nothing will be printed, for example, the above will produce just <strong>"Hello, "</strong>. But you can use something like this:'
        . '<pre>Dear {{ echo $first_name ? $first_name : "friend" }}</pre>'
        . 'It will be replaces with <strong>"Dear John"</strong> if the first name is "John" and <strong>"Dear friend"</strong> if the first name is not set.',
    'PARSER_FIRST_NAME_DESCR' => 'Contains first name of the client or NULL if it is not known',
    'PARSER_MIDDLE_NAME_DESCR' => 'Contains middle name of the client or NULL if it is not known',
    'PARSER_LAST_NAME_DESCR' => 'Contains last name of the client or NULL if it is not known',
    'PARSER_SHORT_NAME_DESCR' => 'First and middle name concatenated if they are known or NULL',
    'PARSER_FULL_NAME_DESCR' => 'First, middle and last name concatenated if they are known or NULL',
    'PARSER_GENDER_DESCR' => 'Variable is set to "male" if the gender of the client is male or "female" if it is female. NULL if gender is not set',
    'PARSER_COMPANY_DESCR' => 'Contains company name or NULL if it is not known',
    'PARSER_POSITION_DESCR' => 'Contains client\'s position or NULL if it is now known',

    // OutgoingController
    'Secret key' => 'Secret key',
    'Error' => 'Error',
    'When sent' => 'When sent',
    'From address' => 'From address',
    'To address' => 'To address',
    'Outgoing filter' => 'Outgoing filter',
    'OUTGOING_SENT_FILTER' => 'Sent messages',
    'OUTGOING_PLANNED_FILTER' => 'Planned messages',

    // SettingController
    'Autodelete' => 'Autodelete',
    'Days' => 'Days',

    // Tours
    'TOUR_MAILBOX_INTRO' => '<p>Start working with CorpNews by sending newsletter template as an email to the application mailbox</p>',
    'TOUR_MAILBOX_INCOMING' => '<p><strong>Incoming</strong> is the folder new mail will go to.</p>',
    'TOUR_MAILBOX_REPLIES' => '<p><strong>Replies</strong> contains client replies to the campaign letters</p>',
    'TOUR_MAILBOX_BOUNCES' => '<p><strong>Bounces</strong> is the delivery failure notifications folder</p>',
    'TOUR_MAILBOX_TEMPLATE' => '<p>When template is received select it in the table and click this button in order to create new mail campaign</p>',
];
