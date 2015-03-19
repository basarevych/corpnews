<?php

return [
    // Layouts
    'APP_TITLE' => 'CorpNews',
    'en_US' => 'English (en_US)',
    'ru_RU' => 'Русский (ru_RU)',
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

    // Data forms
    'Profile' => 'Профиль',

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
    'ERROR_CREATE_FROM_TEMPLATE' => 'Не удалось создать письмо по шаблону (%id%)',
    'ERROR_SEND_LETTER' => 'Не удалось отправить письмо: %exception%',

    // Csrf validator
    'The form submitted did not originate from the expected site' => 'Отправленная форма не принадлежит ожидаемому сайту',

    // NotEmpty validator
    'Value is required and can\'t be empty' => 'Значение обязательно и не может быть пустым',

    // EmailAddress validator
    'The input is not a valid email address. Use the basic format local-part@hostname' => 'Значение не является правильным адресом электронной почты. Используйте формат local-part@hostname',
    '\'%hostname%\' is not a valid hostname for the email address' => '\'%hostname%\' не является правильным именем хоста для электоронной почты',
    '\'%hostname%\' does not appear to have any valid MX or A records for the email address' => '\'%hostname%\' не имеет МХ или А записей для электронной почты',
    '\'%hostname%\' is not in a routable network segment. The email address should not be resolved from public network' => '\'%hostname%\' не находится в маршрутизируемом сегменте сети',
    '\'%localPart%\' can not be matched against dot-atom format' => '\'%localPart%\' не соответствует формату dot-atom',
    '\'%localPart%\' can not be matched against quoted-string format' => '\'%localPart%\' не соответсвтует формату quoted-string',
    '\'%localPart%\' is not a valid local part for the email address' => '\'%localPart%\' не является правильной локальной частью адреса электронной почты',
    'The input exceeds the allowed length' => 'Значение превышает разрешенную длину',

    // Hostname validator
    'The input appears to be a DNS hostname but the given punycode notation cannot be decoded' => 'Значение является правильной записью DNS, но punycode запись не может быть расшифрована',
    'The input appears to be a DNS hostname but contains a dash in an invalid position' => 'Значение является записью DNS хоста, но содержит дефис в неправильной позиции',
    'The input does not match the expected structure for a DNS hostname' => 'Значение не соответствует ожидаемой структуре DNS записи хоста',
    'The input appears to be a DNS hostname but cannot match against hostname schema for TLD \'%tld%\'' => 'Значение является DNS записью, но не соответствует схеме для TLD \'%tld%\'',
    'The input does not appear to be a valid local network name' => 'Значение не является правильной записью локальной сети',
    'The input does not appear to be a valid URI hostname' => 'Значение не является правильным URI хоста',
    'The input appears to be an IP address, but IP addresses are not allowed' => 'Значение является IP адресом, но использование IP адресов не разрешено',
    'The input appears to be a local network name but local network names are not allowed' => 'Значение является частью локальной сети, но использование локальных сетей не разрешено',
    'The input appears to be a DNS hostname but cannot extract TLD part' => 'Значение является записью DNS, но не удается определить TLD часть',
    'The input appears to be a DNS hostname but cannot match TLD against known list' => 'Значение является DNS записью, но содержит неизвестную часть TLD',

    // EntityNotExists/DocumentNotExists validators
    'Value is already in the database' => 'Значение уже существует в базе данных',
];
