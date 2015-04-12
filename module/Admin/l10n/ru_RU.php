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
    'Campaign tags' => 'Теги кампаний',
    'Mail campaigns' => 'Почтовые кампании',
    'Data' => 'Данные',
    'Data forms' => 'Формы данных',
    'Import/Export' => 'Импорт/Экспорт',
    'System log' => 'Системный журнал',
    'Settings' => 'Настройки',
    'Email sender' => 'Отправка почты',

    // IndexController
    'Introduction' => 'Вступление',
    'CORPNEWS_INTRO' => 'Основная сущность, используемая CorpNews, - это <strong>почтовая кампания</strong>.'
        . ' Каждая кампания имеет один или более <strong>шаблонов</strong> писем,'
        . ' которые будут отправлены <strong>кленту</strong> во время кампании.'
        . '</p></p>'
        . 'Объединяйте ваших клиентов в <strong>группы</strong> (каждый клиент может входить в неограниченное число групп)'
        . ' и выберите, какие группы получат письмо.'
        . '</p></p>'
        . 'Вы можете назначить <strong>теги</strong> вашей кампании'
        . ' и клиенты смогут (от-)подписаться на эти теги.'
        . '</p></p>'
        . 'Обратите внимание, что в CorpNews нет редактора электронной почты.'
        . ' Вместо этого вы используете свой привычный почтовый клиент чтобы создать шаблон,'
        . ' а затем отправляете его на почтовый ящик системы, '
        . ' где вы можете его выбрать и создать кампанию.'
        . '</p><p>'
        . 'Письмо может содержать специальные вставки для того, чтобы сделать шаблон динамичным.'
        . ' Например, вы может написать в шаблоне "Здравствуйте, {{ first_name }}!" и этот текст будет заменен на'
        . ' "Здравствуйте, Иван!" (если имя клиента - Иван)'
        . '</p></p>'
        . 'Другое свойство CorpNews - это формы данных.'
        . ' Например, форма данных <strong>Profile</strong> позволяет клиенту редактировать свой собственный профиль,'
        . ' а форма <strong>Subscription</strong> - отменить подписку на вашу рассылку.',
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
    'Empty group' => 'Очистить группу',
    'Delete group' => 'Удалить группу',
    'Number of clients' => 'Число клиентов',
    'Empty' => 'Очистить',
    'CONFIRM_EMPTY_GROUP' => 'Исключить всех клиентов из выбранной группы (групп)?',
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
    'Errors' => 'Ошибки',
    'Email bounced' => 'Письмо возвратилось',
    'Yes' => 'Да',
    'No' => 'Нет',
    'Groups' => 'Группы',
    'CONFIRM_DELETE_CLIENT' => 'Удалить выбранного клиента (клиентов)?',

    // CampaignController
    'Status filter' => 'Фильтр статуса',
    'STATUS_CREATED' => 'Создано',
    'STATUS_TESTED' => 'Протестировано',
    'STATUS_QUEUED' => 'Поставлено в очередь',
    'STATUS_STARTED' => 'Рассылка почты',
    'STATUS_PAUSED' => 'Поставлено на паузу',
    'STATUS_FINISHED' => 'Завершено',
    'STATUS_ARCHIVED' => 'В архиве',
    'Apply filter' => 'Применить фильтр',
    'Templates' => 'Шаблоны',
    'Edit campaign' => 'Редактировать кампанию',
    'Launch campaign' => 'Запустить кампанию',
    'Test campaign' => 'Тестировать кaмпанию',
    'Pause campaign' => 'Поставить на паузу',
    'Archive campaign' => 'Архивировать кампанию',
    'Delete campaign' => 'Удалить кампанию',
    'Pause' => 'Пауза',
    'Archive' => 'В архив',
    'When deadline' => 'Когда крайний срок',
    'When created' => 'Когда создано',
    'When started' => 'Когда начато',
    'When finished' => 'Когда закончено',
    'Status' => 'Статус',
    'Percent' => 'Процент',
    'Tester' => 'Тестировщик',
    'Send to' => 'Кому отправить',
    'Send test letter' => 'Отправить тестовое письмо',
    'Launch' => 'Запустить',
    'Letter has been sent' => 'Письмо было отправлено',
    'Variable substitution failed' => 'Замена переменных не удалась',
    'Campaign test failed' => 'Тестирование камании завершилось неудачно',
    'Campaign statistics' => 'Статистика кампании',
    'Parameter' => 'Параметр',
    'Value' => 'Значение',
    'Total number of clients' => 'Общее число клиентов',
    'Number of clients received letter' => 'Число клиентов, получивших письмо',
    'Form opened' => 'Форма открыта',
    'Form saved' => 'Форма сохранена',
    'Start campaign' => 'Начать кампанию',
    'CAMPAIGN_DEADLINE_HELP' => 'Кампания будет автоматически архивирована после этой даты'
        . ' и все отправленные клиенту ссылки перестанут работать.',
    'CAMPAIGN_NO_TESTERS' => 'Тестеровщики не найдены.'
        . '<br><br>'
        . 'Пожалуйста, добавьте клиентов в группу "Testers"',
    'CAMPAIGN_NO_GROUPS' => 'Этой кампании не были назначены группы пользователей',
    'CAMPAIGN_TEST_FORMS' => 'Редактируйте формы данных выбранного пользователя:',
    'CONFIRM_START_CAMPAIGN' => 'Запустить выбранную кампанию и начать рассылать почту?',
    'CONFIRM_CONTINUE_CAMPAIGN' => 'Продолжить рассылать почту для выбранной кампании?',
    'ALERT_CAMPAIGN_NOT_TESTED' => 'Кампания не была протестирована!',
    'CAMPAIGN_ALREADY_LAUNCHED' => 'Кампания уже запущена',
    'CONFIRM_PAUSE_CAMPAIGN' => 'Поставить на паузу выбранную кампанию (кампании)?',
    'CONFIRM_ARCHIVE_CAMPAIGN' => 'Архивировать выбранную кампанию (кампании)?'
        . '<br><br>Ссылки на формы данных, полученные клиентами, перестанут работать.',
    'CONFIRM_DELETE_CAMPAIGN' => 'Удалить выбранную кампанию (кампании)?',

    // TagController
    'Description' => 'Описание',
    'Create tag' => 'Создать тег',
    'Delete tag' => 'Удалить тег',
    'TAG_DESCRIPTION_HELP' => 'Это то, что увидит клиент, когда будет отписываться от рассылки',
    'CONFIRM_DELETE_TAG' => 'Удалить выбранный тег (теги)?',

    // DocumentController
    'Selected data form' => 'Выбранная форма данных',
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
    'System email address' => 'Адрес электронной почты системы',
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
    'Execute' => 'Выполнить',
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
    'Download' => 'Скачать',
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
    'STATUS_SENT' => 'Отправлено',
    'STATUS_SKIPPED' => 'Пропущено',
    'STATUS_FAILED' => 'Ошибка',
    'Secret key' => 'Секретный ключ',
    'Error' => 'Ошибка',
    'When created' => 'Когда создано',
    'When processed' => 'Когда обработано',
    'From address' => 'Адрес "От кого"',
    'To address' => 'Адрес "Кому"',
    'Outgoing filter' => 'Фильтр исходящих',

    // SettingController
    'Save' => 'Сохранить',
    'Mailbox settings' => 'Настройки почтового ящика',
    'Autodelete' => 'Автоудаление',
    'Days' => 'Дни',
    'Email sender settings' => 'Настройки отправки почты',
    'Send interval' => 'Интервал отправки',
    'Seconds' => 'Секунды',

    // SyslogController
    'Log level filter' => 'Фильтр уровня записи',
    'LEVEL_INFO' => 'LEVEL_INFO',
    'LEVEL_ERROR' => 'LEVEL_ERROR',
    'LEVEL_CRITICAL' => 'LEVEL_CRITICAL',
    'When happened' => 'Когда произошло',
    'Source name' => 'Имя источника',
    'Source ID' => 'ID источника',
    'Level' => 'Уровень',
    'Clear log' => 'Очистить журнал',
    'CONFIRM_CLEAR_SYSLOG' => 'Удалить все записи системного журнала?',

    // ImportExportController
    'Presets' => 'Пресеты',
    'IMPORT_EXPORT_MINIMUM' => 'Минимум',
    'IMPORT_EXPORT_FULL_NAME' => 'Полное имя',
    'IMPORT_EXPORT_MAXIMUM' => 'Максимум',
    'Available fields' => 'Доступные поля',
    'Reorder columns' => 'Измените порядок колонок',
    'Actions' => 'Действия',
    'Import' => 'Импорт',
    'Export' => 'Экспорт',
    'Import data' => 'Импорт данных',
    'IMPORT_NO_EMAIL' => 'Для импорта поле электронной почты обязательно',
    'Encoding' => 'Кодировка',
    'Import preview' => 'Предварительный просмотр импорта',
    'Accept results' => 'Принять результаты',
    'Cancel import' => 'Отменить импорт',
    'No groups' => 'Без групп',

    // Tours
    'Parser' => 'Парсер',
    'TOUR_PARSER_INTRO' => '<p>Вы можете использовать эти выражения в шаблоне письма</p>',
    'TOUR_MAILBOX_INTRO' => '<p>Начните работу с CorpNews с отправки шаблона новостного письма как сообщения электронной почты на ящик приложения.</p>',
    'TOUR_MAILBOX_TEMPLATE' => '<p>Когда шаблон принят, отметьте его в таблице и нажмите эту кнопку для того, чтобы создать новую почтовую кампанию.</p>',
    'Outgoing' => 'Исходящие',
    'TOUR_OUTGOING_INTRO' => '<p>Вся почта, когда-либо отправленная системой перечислена в этой таблице.</p>'
        . '<p>Также здесь указана почта, которая только запланирована на отправку (для новых почтовых кампаний).</p>',
    'TOUR_GROUPS_INTRO' => '<p>Вы можете объединять клиентов в группы</p>'
        . '<p>Каждый клиент может быть включен в неограниченное число групп</p>',
    'TOUR_CLIENTS_EDIT_CLIENT' => '<p>Кликните почтовый адрес клиента для того, чтобы редактировать его запись</p>'
        . '<p>Или кликните любую форму в "Запоненных формах" для того, чтобы открыть эту форму данного клиента в режиме администратора</p>',
    'Tags' => 'Теги',
    'TOUR_TAGS_INTRO' => '<p>Почтовой рассылке может быть назначено некоторое число тегов.</p>'
        . '<p>Клиент может открыть свою форму подписки и выбрать какие теги (темы) он желает получать.</p>'
        . '<p>По умолчанию все клиенты подписаны на теги, которые вы создаете</p>',
    'Campaigns' => 'Кампании',
    'TOUR_CAMPAIGNS_INTRO' => '<p>Вы можете управлять вашими почтовыми кампаниями здесь.</p>'
        . '<p>Пожалуйста, не забывайте протестировать вышу кампанию, прежде чем начинать массовую рассылку</p>',
    'TOUR_IMPORT_EXPORT_INTRO' => '<p>Эта страница позволит вам создавать файлы экспорта и принимать файлы импорта для форм данных клиентов<p>',
];
