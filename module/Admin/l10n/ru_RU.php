<?php

return [
    // Admin layout
    'Mail' => 'Почта',
    'Message parser' => 'Парсер сообщений',
    'Mailbox' => 'Почтовый ящик',
    'Outgoing messages' => 'Исходящие сообщения',
    'Campaign' => 'Кампания',
    'Client groups' => 'Группы клиентов',
    'Clients' => 'Клиенты',
    'Mail campaigns' => 'Почтовые кампании',
    'Data forms' => 'Формы данных',
    'System log' => 'Системный журнал',
    'Settings' => 'Настройки',
    'Mailbox settings' => 'Настройки почтового ящика',

    // IndexController
    'MESSAGE_PARSER_HELP' => 'Эта страница содержит перечень функций парсера почты.'
        . ' Пожалуйста, прочтите эту страницу прежде, чем создавать шаблон почтовой кампании.',
    'MAILBOX_HELP' => 'Это интерфейс к почтовому ящику IMAP, используемым CorpNews',
    'OUTGOING_HELP' => 'Таблица исходящих сообщений. Содержит и отправленные сообщения, и сообщения, которые только были запланированы к отправке.',
    'GROUPS_HELP' => 'Когда вы начинаете почтовую кампанию, вы устанавливаете, какие группы клиентов получат письмо. Эта страница задает такие группы.',
    'CLIENTS_HELP' => 'Цель почтовой кампании - это клиент. Эта страница позволяет вам управлять клиентами и объединять их в группы.',
    'CAMPAIGNS_HELP' => 'Страница управления почтовыми кампаниями. Позволяет вам редактировать/стартовать/останавливать/смотреть статистику почтовых кампаний.',
    'DATA_FORMS_HELP' => 'Данные клиентов хранятся в формах данных. Эта страница предоставляет доступ к этим формам данных.',
    'SYSTEM_LOG_HELP' => 'Важные события записываются в системный журнал.',
    'SETTINGS_MAILBOX_HELP' => 'Установите время, после которого старая почта будет автоматически удалена, чтобы ограничить размер почтового ящика.',
    'Important log messages' => 'Важные сообщения журнала событий',
    'No messages' => 'Нет сообщений',

    // AuthController
    'Restricted area' => 'Запретная зона',
    'Login' => 'Логин',
    'Password' => 'Пароль',
    'Sign in' => 'Войти',
    'Invalid login or password' => 'Неправильный логин или пароль',

    // GroupController
    'Create group' => 'Создать группу',
    'Edit group' => 'Редактировать группу',
    'Delete group' => 'Удалить группу',
    'Number of clients' => 'Число клиентов',
    'CANNOT_EDIT_SYSTEM_GROUP' => 'Нельзя редактирвать системную группу',
    'CONFIRM_DELETE_GROUP' => 'Удалить выбранную группу (группы)?',
    'CANNOT_DELETE_SYSTEM_GROUPS' => 'NOTE: Системные группы не могут быть удалены',

    // ClientController
    'Filled forms' => 'Заполненные формы',
    'Table actions' => 'Действия над таблицей',
    'Create client' => 'Создать клиента',
    'Edit client' => 'Редактировать клиента',
    'Delete client' => 'Удалить клиента',
    'Email address' => 'Электронная почта',
    'Email bounced' => 'Письмо возвратилось',
    'Groups' => 'Группы',
    'CONFIRM_DELETE_CLIENT' => 'Удалить выбранного клиента (клиентов)?',
    'TOUR_CLIENTS_EDIT_CLIENT' => '<p>Кликните почтовый адрес клиента для того, чтобы редактировать его запись</p>'
        . '<p>Или кликните любую форму в "Запоненных формах" для того, чтобы открыть эту форму данного клиента в режиме администратора</p>',

    // CampaignController
    'Status filter' => 'Фильтр статуса',
    'STATUS_CREATED' => 'Создано',
    'STATUS_TESTED' => 'Протестировано',
    'STATUS_QUEUED' => 'Поставлено в очередь',
    'STATUS_STARTED' => 'Рассылка начата',
    'STATUS_PAUSED' => 'Поставлено на паузу',
    'STATUS_FINISHED' => 'Завершено',
    'Apply filter' => 'Применить фильтр',
    'Templates' => 'Шаблоны',
    'Edit campaign' => 'Редактировать кампанию',
    'Launch campaign' => 'Запустить кампанию',
    'Test campaign' => 'Тестировать кaмпанию',
    'Delete campaign' => 'Удалить кампанию',
    'Status' => 'Статус',
    'When deadline' => 'Когда крайний срок',
    'When created' => 'Когда создано',
    'When started' => 'Когда начато',
    'When finished' => 'Когда закончено',
    'Tester' => 'Тестировщик',
    'Send to' => 'Кому отправить',
    'Send test letter' => 'Отправить тестовое письмо',
    'Launch' => 'Запустить',
    'Letter has been sent' => 'Письмо было отправлено',
    'Variable substitution failed' => 'Замена переменных не удалась',
    'Campaign test failed' => 'Тестирование камании завершилось неудачно',
    'Start campaign' => 'Начать кампанию',
    'CAMPAIGN_NO_TESTERS' => 'Тестеровщики не найдены.'
        . '<br><br>'
        . 'Пожалуйста, добавьте клиентов в группу "Testers"',
    'CAMPAIGN_TEST_FORMS' => 'Редактируйте формы данных выбранного пользователя:',
    'CONFIRM_START_CAMPAIGN' => 'Запустить выбранную кампанию?',
    'ALERT_CAMPAIGN_NOT_TESTED' => 'Кампания не была протестирована!',
    'CAMPAIGN_ALREADY_LAUNCHED' => 'Кампания уже запущена',
    'CONFIRM_DELETE_CAMPAIGN' => 'Удалить выбранную кампанию (кампании)?',

    // DocumentController
    'Available data forms' => 'Доступные формы данных',
    'When updated' => 'Когда обновлено',
    'First name' => 'Имя',
    'Middle name' => 'Отчество',
    'Last name' => 'Фамилия',
    'Gender' => 'Пол',
    'male' => 'Мужской',
    'female' => 'Женский',
    'Company' => 'Компания',
    'Position' => 'Должность',
    'TOUR_DATA_FORMS_ADMIN_ACCESS' => 'Кликните почтовый адрес любого клинта для того, чтобы открыть его форму данных в режиме администратора',
    'TOUR_DATA_FORMS_SWITCH' => 'Здесь вы можете переключать текущую форму данных',

    // MailboxController
    'Incoming' => 'Входящие',
    'Replies' => 'Ответы',
    'Bounces' => 'Возвращенные письма',
    'Delete letter' => 'Удалить письмо',
    'Reanalyze letter' => 'Повторно анализировать',
    'UID' => 'UID',
    'Date' => 'Дата',
    'From' => 'От',
    'Subject' => 'Тема',
    'Cancel' => 'Отменить',
    '(No subject)' => '(Без темы)',
    'Create campaign' => 'Создать кампанию',
    'CAN_NOT_CREATE_CAMPAIGN' => 'Письмо (письма) содержит структурную или синтакическую ошибку. Невозможно создать кампанию',
    'CONFIRM_CREATE_CAMPAIGN' => 'Создать почтовую кампанию с выбранным письмом (письмами)?',
    'CONFIRM_DELETE_LETTER' => 'Удалить выбранное письмо (письма)?',
    'CONFIRM_REANALYZE_LETTER' => 'Сбросить статус выбранного письма (писем)?<br><br>Сброшенные письма вскоре снова появятся в одной из папок почтового ящика',

    // LetterController
    'Use as template' => 'Использовать как шаблон',
    'Close' => 'Закрыть',
    'Loading...' => 'Загружается...',
    'HTML' => 'HTML',
    'Text' => 'Текст',
    'Attachments' => 'Приложения',
    'Analysis log' => 'Журнал анализа',
    'Source' => 'Источник',
    'Preview' => 'Предпросмотр',
    'Name' => 'Имя',
    'Type' => 'Тип',
    'Size' => 'Размер',
    'No preview available' => 'Предпросмотр не доступен',
    'Letter check status' => 'Статус проверки письма',
    'Success' => 'Успех',
    'Analysis error' => 'Ошибка анализа',
    'Syntax error' => 'Ошибка синтаксиса',

    // ParserController
    'Message parser functions' => 'Функции парсера сообщений',
    'PARSER_SYNTAX_TITLE' => 'Синтаксис парсера',
    'PARSER_SYNTAX_BODY' => '<p>Общий синтаксис парсера:'
        . '<pre>{{ function_name }}</pre>'
        . 'Или'
        . '<pre>{{ function_name | arg1 | ... | argN }}</pre>'
        . 'Парсер выполнит функцию <em>function_name</em> с аргументами и заменит {{ ... }} на текст ее вывода.'
        . '</p><p>'
        . '<pre>Hello, {{ first_name | Dear friend }}</pre>'
        . 'Здесь мы вызываем функцию <strong>first_name</strong> с аргументом "Dear friend".'
        . 'Конкретно эта функция печатает имя клиента, которому мы пишем. Аргумент - это строка, которая будет напечатана, если имя неизвестно.'
        . '</p><p>'
        . 'Код, приведенный выше, будет заменен на <strong>Hello, John</strong>, если имя - "John", и <strong>Hello, Dear friend</strong>, если имя неизвестно.' 
        . '</p>',
    'PARSER_FIRST_NAME_DESCR' => '<pre>{{ first_name | Default string }}</pre>'
        . 'Печатает имя клиента или "Default string" (опционально), если оно неизвестно',
    'PARSER_MIDDLE_NAME_DESCR' => '<pre>{{ middle_name | Default string }}</pre>'
        . 'Печатает отчество клиента или "Default sring" (опционально), если оно неизвестно',
    'PARSER_LAST_NAME_DESCR' => '<pre>{{ last_name | Default string }}</pre>'
        . 'Печатает фамилию клиента или "Default string" (опционально), если онa неизвестнa',
    'PARSER_SHORT_NAME_DESCR' => '<pre>{{ short_full_name | Default string }}</pre>'
        . 'Печатает имя и отчество клиента, если они известны, или "Default string" (опционально)',
    'PARSER_LONG_NAME_DESCR' => '<pre>{{ long_full_name | Default string }}</pre>'
        . 'Печатает имя, отчество и фамилию клиента, если они известны, или "Default string" (опционально)',
    'PARSER_GENDER_DESCR' => '<pre>{{ gender | Male string | Female string | Default string }}</pre>'
        . 'Печатает "Male string" (обязательный аргумент), если пол клиента мужской, или "Female string" (обязательный аргумент), если он женский. Если пол неизвестен, печатает "Default string" (опционально)',
    'PARSER_COMPANY_DESCR' => '<pre>{{ company | Default string }}</pre>'
        . 'Печатает название компании или "Default string" (опционально), если оно неизвестно',
    'PARSER_POSITION_DESCR' => '<pre>{{ position | Default string }}</pre>'
        . 'Печатает должность клиента или "Default string" (опционально), если оно неизвестно',
    'PARSER_DATA_FORM_LINK_DESCR' => '<pre>{{ data_form_link | form_name | Link text }}</pre>'
        . 'Печатает ссылку (&lt;a&gt;) на форму данных <strong>form_name</strong> для данного клиента. Оба аргумента обязательны.',
    'AVAILABLE_FORMS_TITLE' => 'Формы данных',
    'AVAILABLE_FORMS_BODY' => 'Список доступных форм данных (для использования в качестве аргумента функции <strong>data_form_link</strong>):',

    // OutgoingController
    'Secret key' => 'Секретный ключ',
    'Error' => 'Ошибка',
    'When sent' => 'Когда отправлено',
    'From address' => 'Адрес "От кого"',
    'To address' => 'Адрес "Кому"',
    'Outgoing filter' => 'Фильтр исходящих',
    'OUTGOING_PLANNED_FILTER' => 'Сообщения, запланированные к отправке',
    'OUTGOING_PROCESSED_FILTER' => 'Обработанные сообщения',
    'OUTGOING_SUCCESSFUL_FILTER' => 'Сообщения, успешно отправленные',
    'OUTGOING_ERRONEOUS_FILTER' => 'Сообщения, которые не удалось отправить',

    // SettingController
    'Autodelete' => 'Автоудаление',
    'Days' => 'Дни',

    // SyslogController
    'Log level filter' => 'Фильтр уровня записи',
    'LEVEL_INFO' => 'LEVEL_INFO',
    'LEVEL_ERROR' => 'LEVEL_ERROR',
    'LEVEL_CRITICAL' => 'LEVEL_CRITICAL',
    'When happened' => 'Когда произошло',
    'Level' => 'Уровень',
    'Clear log' => 'Очистить журнал',
    'CONFIRM_CLEAR_SYSLOG' => 'Удалить все записи системного журнала?',

    // Tours
    'TOUR_MAILBOX_INTRO' => '<p>Начните работу с CorpNews с отправки шаблона новостного письма как сообщения электронной почты на ящик приложения.</p>',
    'TOUR_MAILBOX_INCOMING' => '<p><strong>Входящие</strong> - это папка, куда попадает новая почта.</p>',
    'TOUR_MAILBOX_REPLIES' => '<p><strong>Ответы</strong> содержит ответы клиентов на письма кампаний.</p>',
    'TOUR_MAILBOX_BOUNCES' => '<p><strong>Возвращенные письма</strong> - это папка уведомлений о недоставленных письмах.</p>',
    'TOUR_MAILBOX_TEMPLATE' => '<p>Когда шаблон принят, отметьте его в таблице и нажмите эту кнопку для того, чтобы создать новую почтовую кампанию.</p>',
];
