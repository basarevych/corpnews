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
