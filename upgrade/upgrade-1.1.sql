DROP TABLE IF EXISTS `nodes_client`;
CREATE TABLE `nodes_client` (
  `id` varchar(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `password` varchar(64) NOT NULL,
  `baseurl` varchar(64) NOT NULL,
  `ip` varchar(32) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;