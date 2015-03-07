<?php

return [
    // Admin layout
    'Campaign' => 'Кампания',
    'Clients' => 'Клиенты',
    'Mailbox' => 'Почтовый ящик',
    'Settings' => 'Настройки',
    'Mailbox settings' => 'Настройки почтового ящика',

    // AuthController
    'Restricted area' => 'Запретная зона',
    'Login' => 'Логин',
    'Password' => 'Пароль',
    'Sign in' => 'Войти',
    'Invalid login or password' => 'Неправильный логин или пароль',

    // ClientController
    'Table actions' => 'Действия над таблицей',
    'Create client' => 'Создать клиента',
    'Delete client' => 'Удалить клиента',
    'Email address' => 'Электронная почта',
    'Email bounced' => 'Письмо возвратилось',
    'Save changes' => 'Сохранить изменения',

    // MailboxController
    'Incoming' => 'Входящие',
    'Replies' => 'Ответы',
    'Bounces' => 'Возвращенные письма',
    'Use as template' => 'Использовать как шаблон',
    'Delete' => 'Удалить',
    'Re-analyze' => 'Повторно анализировать',
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
    'Letter parse status' => 'Статус анализа письма',
    'Success' => 'Успех',
    'Failure' => 'Неудача',
    'CONFIRM_DELETE_LETTER' => 'Удалить выбранное письмо (письма)?',
    'CONFIRM_REANALYZE_LETTER' => 'Сбросить статус выбранного письма (писем)?<br><br>Сброшенные письма вскоре снова появятся в одной из папок почтового ящика',
    'Execute' => 'Выполнить',

    // SettingController
    'Autodelete' => 'Автоудаление',
    'Days' => 'Дни',

    // Tours
    'TOUR_MAILBOX_INTRO' => '<p>Начните работу с CorpNews с отправки шаблона новостного письма как сообщения электронной почты на ящик приложения.</p>',
    'TOUR_MAILBOX_INCOMING' => '<p><strong>Входящие</strong> - это папка, куда попадает новая почта.</p>',
    'TOUR_MAILBOX_REPLIES' => '<p><strong>Ответы</strong> содержит ответы клиентов на письма кампаний.</p>',
    'TOUR_MAILBOX_BOUNCES' => '<p><strong>Возвращенные письма</strong> - это папка уведомлений о недоставленных письмах.</p>',
    'TOUR_MAILBOX_TEMPLATE' => '<p>Когда шаблон принят, отметьте его в таблице и нажмите эту кнопку для того, чтобы создать новую почтовую кампанию.</p>',

    // Csrf validator
    'The form submitted did not originate from the expected site' => 'Отправленная форма не принадлежит ожидаемому сайту',

    // NotEmpty validator
    'Value is required and can\'t be empty' => 'Значение обязательно и не может быть пустым',

    // EntityNotExists/DocumentNotExists validators
    'Value is already in the database' => 'Значение уже существует в базе данных',
];
