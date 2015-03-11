<?php

return [
    // Admin layout
    'Mail' => 'Почта',
    'Mailbox' => 'Почтовый ящик',
    'Campaign' => 'Кампания',
    'Clients' => 'Клиенты',
    'Data forms' => 'Формы данных',
    'Settings' => 'Настройки',
    'Mailbox settings' => 'Настройки почтового ящика',

    // AuthController
    'Restricted area' => 'Запретная зона',
    'Login' => 'Логин',
    'Password' => 'Пароль',
    'Sign in' => 'Войти',
    'Invalid login or password' => 'Неправильный логин или пароль',

    // ClientController
    'Filled forms' => 'Заполненные формы',
    'Table actions' => 'Действия над таблицей',
    'Create client' => 'Создать клиента',
    'Edit client' => 'Редактировать клиента',
    'Delete client' => 'Удалить клиента',
    'Email address' => 'Электронная почта',
    'Email bounced' => 'Письмо возвратилось',
    'CONFIRM_DELETE_CLIENT' => 'Удалить выбранного клиента (клиентов)?',

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
];
