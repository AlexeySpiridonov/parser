
DROP TABLE IF EXISTS `items`;

CREATE TABLE `items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(250) DEFAULT NULL,
  `name` varchar(250) DEFAULT NULL,
  `email` varchar(56) DEFAULT NULL,
  `domen` varchar(56) DEFAULT NULL,
  `url` varchar(256) DEFAULT NULL,
  `update` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `domen` (`domen`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
