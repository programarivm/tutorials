DROP DATABASE IF EXISTS kiss_acl;

CREATE DATABASE kiss_acl;

USE kiss_acl;

GRANT ALL PRIVILEGES ON kiss_acl.* TO 'kiss_acl_user'@'localhost' IDENTIFIED BY 'password';

CREATE TABLE IF NOT EXISTS acl_role (
    `id` mediumint UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(64) NOT NULL,
    `slug` varchar(64) NOT NULL,
    `description` varchar(256) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS acl_resource (
    `id` mediumint UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(64) NOT NULL,
    `slug` varchar(64) NOT NULL,
    `description` varchar(256) NULL,
    `type` varchar(64) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS acl_entry (
    `id` mediumint UNSIGNED NOT NULL AUTO_INCREMENT,
    `acl_role_id` mediumint UNSIGNED NOT NULL,
    `acl_resource_id` mediumint UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`acl_role_id`) REFERENCES acl_role(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`acl_resource_id`) REFERENCES acl_resource(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
