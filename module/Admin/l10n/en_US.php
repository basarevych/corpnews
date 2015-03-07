<?php

return [
    // Admin layout
    'Campaign' => 'Campaign',
    'Clients' => 'Clients',
    'Mailbox' => 'Mailbox',
    'Settings' => 'Settings',
    'Mailbox settings' => 'Mailbox settings',

    // AuthController
    'Restricted area' => 'Restricted area',
    'Login' => 'Login',
    'Password' => 'Password',
    'Sign in' => 'Sign in',
    'Invalid login or password' => 'Invalid login or password',

    // ClientController
    'Table actions' => 'Table actions',
    'Create client' => 'Create client',
    'Edit client' => 'Edit client',
    'Delete client' => 'Delete client',
    'Email address' => 'Email address',
    'Email bounced' => 'Email bounced',
    'Save changes' => 'Save changes',
    'Delete' => 'Delete',

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
    'Letter parse status' => 'Letter parse status',
    'Success' => 'Success',
    'Failure' => 'Failure',
    'CONFIRM_DELETE_LETTER' => 'Delete selected letter(s)?',
    'CONFIRM_REANALYZE_LETTER' => 'Reset status of selected letter(s)?<br><br>Reset letters will soon reappear in one of the mailbox\'es folder again',
    'Execute' => 'Execute',

    // SettingController
    'Autodelete' => 'Autodelete',
    'Days' => 'Days',

    // Tours
    'TOUR_MAILBOX_INTRO' => '<p>Start working with CorpNews by sending newsletter template as an email to the application mailbox</p>',
    'TOUR_MAILBOX_INCOMING' => '<p><strong>Incoming</strong> is the folder new mail will go to.</p>',
    'TOUR_MAILBOX_REPLIES' => '<p><strong>Replies</strong> contains client replies to the campaign letters</p>',
    'TOUR_MAILBOX_BOUNCES' => '<p><strong>Bounces</strong> is the delivery failure notifications folder</p>',
    'TOUR_MAILBOX_TEMPLATE' => '<p>When template is received select it in the table and click this button in order to create new mail campaign</p>',

    // Csrf validator
    'The form submitted did not originate from the expected site' => 'The form submitted did not originate from the expected site',

    // NotEmpty validator
    'Value is required and can\'t be empty' => 'Value is required and can\'t be empty',

    // EntityNotExists/DocumentNotExists validators
    'Value is already in the database' => 'Value is already in the database',
];
