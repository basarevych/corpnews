DROP TABLE IF EXISTS `letters`;
DROP TABLE IF EXISTS `templates`;
DROP TABLE IF EXISTS `campaigns`;
DROP TABLE IF EXISTS `client_groups`;
DROP TABLE IF EXISTS `clients`;
DROP TABLE IF EXISTS `groups`;
DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `type` enum('string', 'integer', 'float', 'boolean', 'datetime') NOT NULL,
    `value_string` varchar(255) NULL,
    `value_integer` int NULL,
    `value_float` float NULL,
    `value_boolean` tinyint(1) NULL,
    `value_datetime` datetime NULL,
    CONSTRAINT `settings_pk` PRIMARY KEY (`id`),
    CONSTRAINT `settings_name_unique` UNIQUE (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `groups` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    CONSTRAINT `groups_pk` PRIMARY KEY (`id`),
    CONSTRAINT `groups_name_unique` UNIQUE (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `clients` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `when_bounced` datetime NULL,
    CONSTRAINT `clients_pk` PRIMARY KEY (`id`),
    CONSTRAINT `clients_email_unique` UNIQUE (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `client_groups` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `client_id` int unsigned NOT NULL,
    `group_id` int unsigned NOT NULL,
    CONSTRAINT `client_groups_pk` PRIMARY KEY (`id`),
    CONSTRAINT `client_groups_client_fk` FOREIGN KEY (`client_id`)
        REFERENCES `clients` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `client_groups_group_fk` FOREIGN KEY (`group_id`)
        REFERENCES `groups` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `campaigns` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `status` enum('created', 'tested', 'queued', 'started', 'done') NOT NULL,
    `when_started` datetime NULL,
    `when_finished` datetime NULL,
    CONSTRAINT `campaign_pk` PRIMARY KEY (`id`),
    CONSTRAINT `campaign_name_unique` UNIQUE (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `templates` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `campaign_id` int unsigned NOT NULL,
    `message_id` varchar(255) NOT NULL,
    `subject` text NULL,
    `headers` mediumtext NULL,
    `body` mediumtext NULL,
    CONSTRAINT `campaign_pk` PRIMARY KEY (`id`),
    CONSTRAINT `templates_campaign_fk` FOREIGN KEY (`campaign_id`)
        REFERENCES `campaigns` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `letters` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `template_id` int unsigned NOT NULL,
    `client_id` int unsigned NULL,
    `key` varchar(255) NOT NULL,
    `when_sent` datetime NULL,
    `to_address` text NOT NULL,
    `subject` text NULL,
    `headers` mediumtext NULL,
    `body` mediumtext NULL,
    CONSTRAINT `campaign_pk` PRIMARY KEY (`id`),
    CONSTRAINT `letters_template_fk` FOREIGN KEY (`template_id`)
        REFERENCES `templates` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `letters_client_fk` FOREIGN KEY (`client_id`)
        REFERENCES `clients` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
