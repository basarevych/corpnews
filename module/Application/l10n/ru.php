<?php

return [
    // Layouts
    'APP_TITLE' => 'CorpNews',
    'en' => 'en - English',
    'ru' => 'ru - Русский',
    'Autodetect' => 'Автоопределение',
    'Tutorial' => 'Обучение',

    // Generic forms
    'REQUIRED FIELD' => 'ОБЯЗАТЕЛЬНОЕ ПОЛЕ',
    'Create' => 'Создать',
    'Save changes' => 'Сохранить изменения',
    'Delete' => 'Удалить',

    // Formats
    'GENERIC_DATETIME_FORMAT' => 'Y-m-d H:i:s P',
    'GENERIC_MOMENT_FORMAT' => 'YYYY-MM-DD HH:mm:ss Z',

    // Help tours
    'TOUR_TEMPLATE' => '<div class="popover help-tour">'
        . '<div class="arrow"></div>'
        . '<h3 class="popover-title"></h3>'
        . '<div class="popover-content"></div>'
        . '<div class="popover-navigation">'
        . '<button class="btn btn-default btn-sm" data-role="prev">« Пред</button>'
        . '<button class="btn btn-default btn-sm" data-role="next">След »</button>'
        . '<button class="pull-right btn btn-default btn-sm" data-role="end">Завершить</button>'
        . '</div>'
        . '</div>',

    // DynamicTable
    'DT_BANNER_LOADING' => 'Загружается... Пожалуйста, подождите',
    'DT_BANNER_EMPTY' => 'Ничего не найдено',
    'DT_BUTTON_PAGE_SIZE' => 'Размер страницы',
    'DT_BUTTON_COLUMNS' => 'Колонки',
    'DT_BUTTON_REFRESH' => 'Обновить',
    'DT_BUTTON_OK' => 'OK',
    'DT_BUTTON_CLEAR' => 'Очистить',
    'DT_BUTTON_CANCEL' => 'Отмена',
    'DT_TITLE_FILTER_WINDOW' => 'Фильтр',
    'DT_LABEL_CURRENT_PAGE' => 'Текущая страница',
    'DT_LABEL_ALL_PAGES' => 'Все страницы',
    'DT_LABEL_PAGE_OF_1' => 'Страница',
    'DT_LABEL_PAGE_OF_2' => 'из #',
    'DT_LABEL_FILTER_LIKE' => 'Строки, похожие на',
    'DT_LABEL_FILTER_EQUAL' => 'Значение, равные',
    'DT_LABEL_FILTER_BETWEEN_START' => 'Значения, большие либо равные',
    'DT_LABEL_FILTER_BETWEEN_END' => 'Значения, меньшие либо равные',
    'DT_LABEL_FILTER_NULL' => 'Включить строки с пустым значением в этой колонке',
    'DT_LABEL_TRUE' => 'Истина',
    'DT_LABEL_FALSE' => 'Ложь',
    'DT_DATE_TIME_FORMAT' => 'YYYY-MM-DD HH:mm:ss Z',

    // Errors
    'Please try again later' => 'Пожалуйста, повторите позднее',
    'Invalid request parameters' => 'Неправильные параметры запроса',
    'Authentication is required' => 'Требуется аутентификация',
    'Access to requested resource is denied' => 'Доступ к запрашиваемому ресурсу запрещен',
    'Requested resource is not found' => 'Запрашиваемый ресурс не найден',
    'Requested method is not implemented' => 'Запрошенный метод не реализован',
    'No additional information available' => 'Дополнительная информация отсутствует',
    'Exception information' => 'Информация исключения',
    'Previous Exception information' => 'Информация предыдущего исключения',
    'Class' => 'Класс',
    'Code' => 'Код',
    'Message' => 'Сообщение',
    'File' => 'Файл',
    'Line' => 'Строка',
    'Stack trace' => 'Трассировка стека',

    // Logger
    'INFO_MAILBOX_CREATED' => 'Создан почтовый ящик',
    'INFO_LETTER_AUTODELETED' => 'Старое письмо автоудалено',
    'INFO_LETTER_BOUNCED' => 'Получено письмо о возврате',
    'INFO_LETTER_WAS_REPLIED' => 'Получен ответ на письмо',
    'INFO_LETTER_PROCESSED' => 'Получено новое письмо',
    'INFO_CAMPAIGN_BEING_PROCESSED' => 'Кампания обрабатывается',
    'INFO_CAMPAIGN_STARTED' => 'Кампания обработана и запущена',
    'INFO_CAMPAIGN_PASSED_DEADLINE' => 'Крайний срок наступил - кампания архивирована',
    'INFO_CAMPAIGN_DONE' => 'Кампания успешно завершена',
    'ERROR_CREATE_FROM_TEMPLATE' => 'Не удалось создать письмо по шаблону',
    'ERROR_SEND_LETTER' => 'Не удалось отправить письмо: %exception%',
    'ERROR_CAMPAIGN_PAUSED' => 'Кампания поставлена на паузу из-за ошибок',

    // EntityNotExists/DocumentNotExists validators
    'Value is already in the database' => 'Значение уже существует в базе данных',

    // Float validator
    'Value is not a float number' => 'Значение не является действительным числом',

    // Integer validator
    'Value is not an integer number' => 'Значение не является натуральным числом',

    // ValuesMatch validator
    'The two values do not match' => 'Два введенных значения не совпадают',
];
