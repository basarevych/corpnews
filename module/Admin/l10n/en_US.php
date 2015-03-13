<?php

return [
    // Admin layout
    'Mail' => 'Mail',
    'Message parser' => 'Message parser',
    'Mailbox' => 'Mailbox',
    'Campaign' => 'Campaign',
    'Client groups' => 'Client groups',
    'Clients' => 'Clients',
    'Data forms' => 'Data forms',
    'Settings' => 'Settings',
    'Mailbox settings' => 'Mailbox settings',

    // AuthController
    'Restricted area' => 'Restricted area',
    'Login' => 'Login',
    'Password' => 'Password',
    'Sign in' => 'Sign in',
    'Invalid login or password' => 'Invalid login or password',

    // GroupController
    'Create group' => 'Create group',
    'Delete group' => 'Create group',
    'Number of clients' => 'Число клиентов',
    'CONFIRM_DELETE_GROUP' => 'Delete selected group(s)?',

    // ClientController
    'Filled forms' => 'Filled forms',
    'Table actions' => 'Table actions',
    'Create client' => 'Create client',
    'Edit client' => 'Edit client',
    'Delete client' => 'Delete client',
    'Email address' => 'Email address',
    'Email bounced' => 'Email bounced',
    'Groups' => 'Groups',
    'CONFIRM_DELETE_CLIENT' => 'Delete selected client(s)?',
    'TOUR_CLIENTS_EDIT_CLIENT' => '<p>Click any client\'s email to edit the client'
        . '<p><p>Or click a form in "Filled forms" to open the form of the client in admin mode</p>',

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
    'Parse error' => 'Parse error',
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
        . '<pre>Hello, {{ echo $full_name }}</pre>'
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
