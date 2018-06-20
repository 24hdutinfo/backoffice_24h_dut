-- Adminer 4.3.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `epreuve`;
CREATE TABLE `epreuve` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libele` varchar(128) NOT NULL,
  `description` text,
  `type_epreuve` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `epreuve` (`id`, `libele`, `description`, `type_epreuve`) VALUES
  (1,	'épreuve IA',	NULL,	1),
  (2,	'épreuve web',	NULL,	1),
  (3,	'épreuve sécurité',	NULL,	1),
  (4,	'general',	NULL,	10);

DROP TABLE IF EXISTS `message`;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `participant`;
CREATE TABLE `participant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lastname` varchar(256) NOT NULL,
  `firstname` varchar(256) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `student_year` int(11) NOT NULL,
  `team_id` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `resultat`;
CREATE TABLE `resultat` (
  `team_id` varchar(64) NOT NULL DEFAULT '',
  `epr_id` int(11) NOT NULL DEFAULT '0',
  `rang` int(11) DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`team_id`,`epr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `team`;
CREATE TABLE `team` (
  `id` varchar(128) NOT NULL,
  `no_ordre` int(11) NOT NULL,
  `team_name` varchar(256) NOT NULL,
  `team_from` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `valid_registration` tinyint(4) NOT NULL DEFAULT '0',
  `registration_paid` tinyint(4) NOT NULL DEFAULT '0',
  `team_password` varchar(256) NOT NULL,
  `team_priority` int(11) DEFAULT '0',
  `contact_email` varchar(256) NOT NULL,
  `team_url` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(256) NOT NULL,
  `passwd` varchar(256) NOT NULL,
  `auth_level` int(11) DEFAULT '10',
  `nom` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

