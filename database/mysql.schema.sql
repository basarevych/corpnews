DROP TABLE IF EXISTS `clients`;
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

CREATE TABLE `clients` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `when_bounced` datetime NULL,
    CONSTRAINT `clients_pk` PRIMARY KEY (`id`),
    CONSTRAINT `clients_email_unique` UNIQUE (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
