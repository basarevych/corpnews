<?php

return [
    // Admin layout
    'Mail' => 'Почта',
    'Message parser' => 'Парсер сообщений',
    'Mailbox' => 'Почтовый ящик',
    'Campaign' => 'Кампания',
    'Client groups' => 'Группы клиентов',
    'Clients' => 'Клиенты',
    'Mail campaigns' => 'Почтовые кампании',
    'Data forms' => 'Формы данных',
    'Settings' => 'Настройки',
    'Mailbox settings' => 'Настройки почтового ящика',

    // AuthController
    'Restricted area' => 'Запретная зона',
    'Login' => 'Логин',
    'Password' => 'Пароль',
    'Sign in' => 'Войти',
    'Invalid login or password' => 'Неправильный логин или пароль',

    // GroupController
    'Create group' => 'Создать группу',
    'Delete group' => 'Удалить группу',
    'CONFIRM_DELETE_GROUP' => 'Удалить выбранную группу (группы)?',

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
    'Delete campaign' => 'Удалить кампанию',
    'When created' => 'Когда создано',
    'When started' => 'Когда начато',
    'When finished' => 'Когда закончено',
    'CAMPAIGN_STEP_1_TITLE' => 'Step 1',
    'CAMPAIGN_STEP_1_TITLE' => 'Шаг 1',
    'CAMPAIGN_STEP_1_TEXT' => 'Создать кампанию',
    'CAMPAIGN_STEP_2_TITLE' => 'Шаг 2',
    'CAMPAIGN_STEP_2_TEXT' => 'Тестировать кампанию',
    'CAMPAIGN_STEP_3_TITLE' => 'Шаг 3',
    'CAMPAIGN_STEP_3_TEXT' => 'Начать кампанию',
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
    'Use as template' => 'Использовать как шаблон',
    'Delete letter' => 'Удалить письмо',
    'Reanalyze letter' => 'Повторно анализировать',
    'UID' => 'UID',
    'Date' => 'Дата',
    'From' => 'От',
    'Subject' => 'Тема',
    'Close' => 'Закрыть',
    'Cancel' => 'Отменить',
    '(No subject)' => '(Без темы)',
    'Loading...' => 'Pагружается...',
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
    'Create campaign' => 'Создать кампанию',
    'CAN_NOT_CREATE_CAMPAIGN' => 'Письмо (письма) содержит структурную или синтакическую ошибку. Невозможно создать кампанию',
    'CONFIRM_CREATE_CAMPAIGN' => 'Создать почтовую кампанию с выбранным письмом (письмами)?',
    'CONFIRM_DELETE_LETTER' => 'Удалить выбранное письмо (письма)?',
    'CONFIRM_REANALYZE_LETTER' => 'Сбросить статус выбранного письма (писем)?<br><br>Сброшенные письма вскоре снова появятся в одной из папок почтового ящика',
    'Execute' => 'Выполнить',

    // ParserController
    'Message parser variables' => 'Переменные парсера сообщений',
    'PARSER_SYNTAX_TITLE' => 'Синтаксис парсера',
    'PARSER_SYNTAX_BODY' => '<p>Общий синтаксис парсера:'
        . '<pre>{{ any_php_code }}</pre>'
        . 'Парсер выполнит <em>any_php_code</em> и заменит его на вывод скрипта.'
        . '</p><p>'
        . 'Несколько примеров:'
        . '<pre>Здравствуйте, {{ echo $full_name }}</pre>'
        . 'Будет заменено на <strong>"Здравствуйте, Петр Иванов"</strong> если имя - "Петр" и фамилия - "Иванов".'
        . 'Если переменная не установлена или не существует, то ничего выведено не будет. Пример выше напечатает только <strong>"Здравствуйте, "</strong>. Но вы можете использовать что-то вроде этого:'
        . '<pre>Дорогой {{ echo $first_name ? $first_name : "друг" }}</pre>'
        . 'Это будет заменено на <strong>"Дорогой Петр"</strong>, если имя - "Петр" и <strong>"Дорогой друг"</strong>, если имя не установлено.',
    'PARSER_FIRST_NAME_DESCR' => 'Содержит имя клиента или NULL, если оно неизвестно',
    'PARSER_MIDDLE_NAME_DESCR' => 'Содержит отчество клиента или NULL, если оно неизвестно',
    'PARSER_LAST_NAME_DESCR' => 'Содержит фамилию клиента или NULL, если она неизвестна',
    'PARSER_SHORT_NAME_DESCR' => 'Объединение имени и отчества клиента, если они известны, или NULL',
    'PARSER_FULL_NAME_DESCR' => 'Объединение имени, отчества и фамилии клиента, если они известны, или NULL',
    'PARSER_GENDER_DESCR' => 'Содержит "male", если пол клиента мужской, или "female", если он женский. NULL, если пол не указан',
    'PARSER_COMPANY_DESCR' => 'Содержит название компании или NULL, если оно неизвестно',
    'PARSER_POSITION_DESCR' => 'Содержит должность клиента или NULL, если она неизвестнa',



    // SettingController
    'Autodelete' => 'Автоудаление',
    'Days' => 'Дни',

    // Tours
    'TOUR_MAILBOX_INTRO' => '<p>Начните работу с CorpNews с отправки шаблона новостного письма как сообщения электронной почты на ящик приложения.</p>',
    'TOUR_MAILBOX_INCOMING' => '<p><strong>Входящие</strong> - это папка, куда попадает новая почта.</p>',
    'TOUR_MAILBOX_REPLIES' => '<p><strong>Ответы</strong> содержит ответы клиентов на письма кампаний.</p>',
    'TOUR_MAILBOX_BOUNCES' => '<p><strong>Возвращенные письма</strong> - это папка уведомлений о недоставленных письмах.</p>',
    'TOUR_MAILBOX_TEMPLATE' => '<p>Когда шаблон принят, отметьте его в таблице и нажмите эту кнопку для того, чтобы создать новую почтовую кампанию.</p>',
];
