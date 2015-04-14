WORK IN PROGRESS
================

CorpNews
========

Demo: http://demo.daemon-notes.com/corpnews


Requirements
------------

* PHP 5.4+

  Required extensions: openssl, pdo, mongo, gearman, imap, mbstring

* RDBMS

  MySQL and PostgreSQL are supported

* NoSQL DB

  MongoDB is supported

* SMTP server, e.g. Postfix with mailboxes in Maildir format.

  By default 127.0.0.1:25 is used

* IMAP server, e.g. Dovecot

  By default 127.0.0.1:143 is used

* Gearman daemon

  By default 127.0.0.1:4730 is used


Installation
------------

```shell
> git clone https://github.com/basarevych/corpnews
> cd corpnews
> ./scripts/install
```

Add to crontab:
```cron
* * * * * sudo -u www php /path/to/corpnews/public.prod/index.php cron > /dev/null 2>&1
```

Replace -u www with your web server user

Example Apache setup:

```
<VirtualHost *:80>
    ServerName corpnews.example.com
    DocumentRoot /path/to/corpnews/public.prod
    <Directory /path/to/corpnews/public.prod>
        DirectoryIndex index.php
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Navigate to http://corpnews.example.com/admin
