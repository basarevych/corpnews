<?php

return [
    // Layouts
    'APP_TITLE' => 'CorpNews',
    'en_US' => 'English (en_US)',
    'ru_RU' => 'Русский (ru_RU)',
    'Autodetect' => 'Autodetect',
    'Tutorial' => 'Tutorial',

    // Generic forms
    'REQUIRED FIELD' => 'REQUIRED FIELD',
    'Create' => 'Create',
    'Save changes' => 'Save changes',
    'Delete' => 'Delete',

    // Formats
    'GENERIC_DATETIME_FORMAT' => 'Y-m-d H:i:s P',
    'GENERIC_MOMENT_FORMAT' => 'YYYY-MM-DD HH:mm:ss Z',

    // Help tours
    'TOUR_TEMPLATE' => '<div class="popover help-tour">'
        . '<div class="arrow"></div>'
        . '<h3 class="popover-title"></h3>'
        . '<div class="popover-content"></div>'
        . '<div class="popover-navigation">'
        . '<button class="btn btn-default btn-sm" data-role="prev">« Prev</button>'
        . '<button class="btn btn-default btn-sm" data-role="next">Next »</button>'
        . '<button class="pull-right btn btn-default btn-sm" data-role="end">End tour</button>'
        . '</div>'
        . '</div>',

    // DynamicTable
    'DT_BANNER_LOADING' => 'Loading... Please wait',
    'DT_BANNER_EMPTY' => 'Nothing found',
    'DT_BUTTON_PAGE_SIZE' => 'Page size',
    'DT_BUTTON_COLUMNS' => 'Columns',
    'DT_BUTTON_REFRESH' => 'Refresh',
    'DT_BUTTON_OK' => 'OK',
    'DT_BUTTON_CLEAR' => 'Clear',
    'DT_BUTTON_CANCEL' => 'Cancel',
    'DT_TITLE_FILTER_WINDOW' => 'Filter',
    'DT_LABEL_CURRENT_PAGE' => 'Current page',
    'DT_LABEL_ALL_PAGES' => 'All pages',
    'DT_LABEL_PAGE_OF_1' => 'Page',
    'DT_LABEL_PAGE_OF_2' => 'of #',
    'DT_LABEL_FILTER_LIKE' => 'Strings like',
    'DT_LABEL_FILTER_EQUAL' => 'Values equal to',
    'DT_LABEL_FILTER_BETWEEN_START' => 'Values greater than or equal to',
    'DT_LABEL_FILTER_BETWEEN_END' => 'Values less than or equal to',
    'DT_LABEL_FILTER_NULL' => 'Include rows with empty value in this column',
    'DT_LABEL_TRUE' => 'True',
    'DT_LABEL_FALSE' => 'False',
    'DT_DATE_TIME_FORMAT' => 'YYYY-MM-DD HH:mm:ss Z',

    // Errors
    'Please try again later' => 'Please try again later',
    'Invalid request parameters' => 'Invalid request parameters',
    'Authentication is required' => 'Authentication is required',
    'Access to requested resource is denied' => 'Access to requested resource is denied',
    'Requested resource is not found' => 'Requested resource is not found',
    'Requested method is not implemented' => 'Requested method is not implemented',
    'No additional information available' => 'No additional information available',
    'Exception information' => 'Exception information',
    'Previous Exception information' => 'Previous Exception information',
    'Class' => 'Class',
    'Code' => 'Code',
    'Message' => 'Message',
    'File' => 'File',
    'Line' => 'Line',
    'Stack trace' => 'Stack trace',

    // Logger
    'INFO_MAILBOX_CREATED' => 'Mailbox created',
    'INFO_LETTER_AUTODELETED' => 'Old letter autodeleted',
    'INFO_LETTER_PROCESSED' => 'New letter received and processed',
    'INFO_CAMPAIGN_BEING_PROCESSED' => 'Campaign is being processed',
    'INFO_CAMPAIGN_STARTED' => 'Campaign processed and started',
    'INFO_CAMPAIGN_PASSED_DEADLINE' => 'Deadline has passed - campaign finished',
    'INFO_CAMPAIGN_DONE' => 'Campaign has finished successfuly',
    'ERROR_CREATE_FROM_TEMPLATE' => 'Could not create letter from template',
    'ERROR_SEND_LETTER' => 'Could not send letter: %exception%',
    'ERROR_CAMPAIGN_PAUSED' => 'Campaign has been paused due to errors',

    // Csrf validator
    'The form submitted did not originate from the expected site' => 'The form submitted did not originate from the expected site',

    // NotEmpty validator
    'Value is required and can\'t be empty' => 'Value is required and can\'t be empty',

    // EmailAddress validator
    'The input is not a valid email address. Use the basic format local-part@hostname' => 'The input is not a valid email address. Use the basic format local-part@hostname',
    '\'%hostname%\' is not a valid hostname for the email address' => '\'%hostname%\' is not a valid hostname for the email address',
    '\'%hostname%\' does not appear to have any valid MX or A records for the email address' => '\'%hostname%\' does not appear to have any valid MX or A records for the email address',
    '\'%hostname%\' is not in a routable network segment. The email address should not be resolved from public network' => '\'%hostname%\' is not in a routable network segment. The email address should not be resolved from public network',
    '\'%localPart%\' can not be matched against dot-atom format' => '\'%localPart%\' can not be matched against dot-atom format',
    '\'%localPart%\' can not be matched against quoted-string format' => '\'%localPart%\' can not be matched against quoted-string format',
    '\'%localPart%\' is not a valid local part for the email address' => '\'%localPart%\' is not a valid local part for the email address',
    'The input exceeds the allowed length' => 'The input exceeds the allowed length',

    // Hostname validator
    'The input appears to be a DNS hostname but the given punycode notation cannot be decoded' => 'The input appears to be a DNS hostname but the given punycode notation cannot be decoded',
    'The input appears to be a DNS hostname but contains a dash in an invalid position' => 'The input appears to be a DNS hostname but contains a dash in an invalid position',
    'The input does not match the expected structure for a DNS hostname' => 'The input does not match the expected structure for a DNS hostname',
    'The input appears to be a DNS hostname but cannot match against hostname schema for TLD \'%tld%\'' => 'The input appears to be a DNS hostname but cannot match against hostname schema for TLD \'%tld%\'',
    'The input does not appear to be a valid local network name' => 'The input does not appear to be a valid local network name',
    'The input does not appear to be a valid URI hostname' => 'The input does not appear to be a valid URI hostname',
    'The input appears to be an IP address, but IP addresses are not allowed' => 'The input appears to be an IP address, but IP addresses are not allowed',
    'The input appears to be a local network name but local network names are not allowed' => 'The input appears to be a local network name but local network names are not allowed',
    'The input appears to be a DNS hostname but cannot extract TLD part' => 'The input appears to be a DNS hostname but cannot extract TLD part',
    'The input appears to be a DNS hostname but cannot match TLD against known list' => 'The input appears to be a DNS hostname but cannot match TLD against known list',

    // EntityNotExists/DocumentNotExists validators
    'Value is already in the database' => 'Value is already in the database',
];
