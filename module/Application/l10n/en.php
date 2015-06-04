<?php

return [
    // Layouts
    'APP_TITLE' => 'CorpNews',
    'en' => 'en - English',
    'ru' => 'ru - Русский',
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
    'INFO_LETTER_BOUNCED' => 'Received bounce letter',
    'INFO_LETTER_WAS_REPLIED' => 'Received reply letter',
    'INFO_LETTER_PROCESSED' => 'New letter received',
    'INFO_CAMPAIGN_BEING_PROCESSED' => 'Campaign is being processed',
    'INFO_CAMPAIGN_STARTED' => 'Campaign processed and started',
    'INFO_CAMPAIGN_PASSED_DEADLINE' => 'Deadline has passed - campaign archived',
    'INFO_CAMPAIGN_DONE' => 'Campaign has finished successfuly',
    'ERROR_CREATE_FROM_TEMPLATE' => 'Could not create letter from template',
    'ERROR_SEND_LETTER' => 'Could not send letter: %exception%',
    'ERROR_CAMPAIGN_PAUSED' => 'Campaign has been paused due to errors',

    // EntityNotExists/DocumentNotExists validators
    'Value is already in the database' => 'Value is already in the database',

    // Float validator
    'Value is not a float number' => 'Value is not a float number',

    // Integer validator
    'Value is not an integer number' => 'Value is not an integer number',

    // ValuesMatch validator
    'The two values do not match' => 'The two values do not match',
];
