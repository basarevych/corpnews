-- MySQL dump 10.13  Distrib 5.6.23, for FreeBSD10.1 (amd64)
--
-- Host: localhost    Database: corpnews
-- ------------------------------------------------------
-- Server version	5.6.23-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `campaign_groups`
--

DROP TABLE IF EXISTS `campaign_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_groups_campaign_fk` (`campaign_id`),
  KEY `campaign_groups_group_fk` (`group_id`),
  CONSTRAINT `campaign_groups_campaign_fk` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `campaign_groups_group_fk` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_groups`
--

LOCK TABLES `campaign_groups` WRITE;
/*!40000 ALTER TABLE `campaign_groups` DISABLE KEYS */;
INSERT INTO `campaign_groups` VALUES (1,1,2);
/*!40000 ALTER TABLE `campaign_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_tags`
--

DROP TABLE IF EXISTS `campaign_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_tags_client_fk` (`campaign_id`),
  KEY `campaign_tags_tag_fk` (`tag_id`),
  CONSTRAINT `campaign_tags_client_fk` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `campaign_tags_tag_fk` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_tags`
--

LOCK TABLES `campaign_tags` WRITE;
/*!40000 ALTER TABLE `campaign_tags` DISABLE KEYS */;
INSERT INTO `campaign_tags` VALUES (1,1,1);
/*!40000 ALTER TABLE `campaign_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` enum('created','tested','queued','started','paused','finished','archived') NOT NULL,
  `when_deadline` datetime DEFAULT NULL,
  `when_created` datetime DEFAULT NULL,
  `when_started` datetime DEFAULT NULL,
  `when_finished` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
INSERT INTO `campaigns` VALUES (1,'Sample campaign','created',NULL,'2015-04-13 06:11:06',NULL,NULL);
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_groups`
--

DROP TABLE IF EXISTS `client_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_groups_client_fk` (`client_id`),
  KEY `client_groups_group_fk` (`group_id`),
  CONSTRAINT `client_groups_client_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `client_groups_group_fk` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_groups`
--

LOCK TABLES `client_groups` WRITE;
/*!40000 ALTER TABLE `client_groups` DISABLE KEYS */;
INSERT INTO `client_groups` VALUES (2,1,1),(3,2,3),(4,3,3),(5,4,3),(6,5,2),(7,6,2),(8,7,2);
/*!40000 ALTER TABLE `client_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `bounced` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'tester@example.com',0),(2,'client1@example.com',0),(3,'client2@example.com',0),(4,'client3@example.com',0),(5,'employee1@example.com',0),(6,'employee2@example.com',0),(7,'employee3@example.com',0);
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groups_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (3,'Clients'),(2,'Employees'),(1,'Testers');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `letters`
--

DROP TABLE IF EXISTS `letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `letters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(10) unsigned NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `status` enum('created','sent','skipped','failed') NOT NULL,
  `when_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `when_processed` datetime DEFAULT NULL,
  `message_id` varchar(255) NOT NULL,
  `from_address` text NOT NULL,
  `to_address` text NOT NULL,
  `subject` text NOT NULL,
  `headers` mediumtext NOT NULL,
  `body` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `letters_message_id_unique` (`message_id`),
  KEY `letters_template_fk` (`template_id`),
  KEY `letters_client_fk` (`client_id`),
  CONSTRAINT `letters_client_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `letters_template_fk` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letters`
--

LOCK TABLES `letters` WRITE;
/*!40000 ALTER TABLE `letters` DISABLE KEYS */;
/*!40000 ALTER TABLE `letters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `secrets`
--

DROP TABLE IF EXISTS `secrets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secrets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `data_form` varchar(255) NOT NULL,
  `secret_key` varchar(255) NOT NULL,
  `when_opened` datetime DEFAULT NULL,
  `when_saved` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `secrets_form_unique` (`campaign_id`,`client_id`,`data_form`),
  UNIQUE KEY `secrets_key_unique` (`secret_key`),
  UNIQUE KEY `secrets_secret_key_unique` (`secret_key`),
  KEY `secrets_client_fk` (`client_id`),
  CONSTRAINT `secrets_campaign_fk` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `secrets_client_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `secrets`
--

LOCK TABLES `secrets` WRITE;
/*!40000 ALTER TABLE `secrets` DISABLE KEYS */;
/*!40000 ALTER TABLE `secrets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('string','integer','float','boolean','datetime') NOT NULL,
  `value_string` varchar(255) DEFAULT NULL,
  `value_integer` int(11) DEFAULT NULL,
  `value_float` float DEFAULT NULL,
  `value_boolean` tinyint(1) DEFAULT NULL,
  `value_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'MailboxAutodelete','integer',NULL,30,NULL,NULL,NULL),(2,'MailInterval','integer',NULL,5,NULL,NULL,NULL);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `descr` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groups_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,'new-product','Information about new products'),(2,'sale','Sale notification');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `templates`
--

DROP TABLE IF EXISTS `templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `subject` text,
  `headers` mediumtext,
  `body` mediumtext,
  PRIMARY KEY (`id`),
  KEY `templates_campaign_fk` (`campaign_id`),
  CONSTRAINT `templates_campaign_fk` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `templates`
--

LOCK TABLES `templates` WRITE;
/*!40000 ALTER TABLE `templates` DISABLE KEYS */;
INSERT INTO `templates` VALUES (1,1,'To the attention of {{ short_full_name | the customer }}','Return-path: <ross@daemon-notes.com>\r\nEnvelope-to: newsletter@daemon-notes.com\r\nDelivery-date: Sun, 12 Apr 2015 19:30:35 +0300\r\nReceived: from ross by coffin.daemon-notes.com with local (Exim 4.85 (FreeBSD))\r\n	(envelope-from <ross@daemon-notes.com>)\r\n	id 1YhKmJ-000Lic-Ne\r\n	for newsletter@daemon-notes.com; Sun, 12 Apr 2015 19:30:35 +0300\r\nReceived: from www by coffin.daemon-notes.com with local (Exim 4.85 (FreeBSD))\r\n	(envelope-from <www@daemon-notes.com>)\r\n	id 1YgQYz-000A2P-8A\r\n	for newsletter@daemon-notes.com; Fri, 10 Apr 2015 07:29:05 +0300\r\nTo: newsletter@daemon-notes.com\r\nSubject: To the attention of {{ short_full_name | the customer }}\r\nX-PHP-Originating-Script: 0:rcube.php\r\nMIME-Version: 1.0\r\nContent-Type: multipart/alternative;\r\n boundary=\"=_20ef3aec043aab1e8338210905a3854b\"\r\nDate: Fri, 10 Apr 2015 07:29:05 +0300\r\nFrom: ross@daemon-notes.com\r\nMessage-ID: <35731aef4aaa5717838c1fa11ba08413@daemon-notes.com>\r\nX-Sender: ross@daemon-notes.com\r\nUser-Agent: Roundcube Webmail/1.1.1\r\n\r\n','\r\n\r\n--=_20ef3aec043aab1e8338210905a3854b\r\nContent-Transfer-Encoding: 7bit\r\nContent-Type: text/plain; charset=US-ASCII\r\n\r\n \r\n\r\nHello, {{ first_name | Dear friend }}\r\n\r\nYou can embed images: \r\n\r\nLinks: {{ data_form_link | profile | Your profile }} and {{\r\ndata_form_link | subscription | Subscription control }} \r\n--=_20ef3aec043aab1e8338210905a3854b\r\nContent-Type: multipart/related;\r\n boundary=\"=_934102e9f090e995ad43d8dbc1f470c7\"\r\n\r\n--=_934102e9f090e995ad43d8dbc1f470c7\r\nContent-Transfer-Encoding: quoted-printable\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n<html><head><meta http-equiv=3D\"Content-Type\" content=3D\"text/html; charset=\r\n=3DUTF-8\" /></head><body style=3D\'font-size: 10pt; font-family: Verdana,Gen=\r\neva,sans-serif\'>\r\n<div class=3D\"pre\" style=3D\"margin: 0; padding: 0; font-family: monospace\">=\r\n<br />Hello, {{ first_name | Dear friend }}<br /><br />You can embed images=\r\n: <br /><br /><img src=3D\"cid:1428640145552751913aac7833436131@daemon-notes=\r\n=2Ecom\" alt=3D\"\" width=3D\"175\" height=3D\"175\" /></div>\r\n<div class=3D\"pre\" style=3D\"margin: 0; padding: 0; font-family: monospace\">=\r\n&nbsp;</div>\r\n<div class=3D\"pre\" style=3D\"margin: 0; padding: 0; font-family: monospace\">=\r\nLinks: {{ data_form_link | profile | Your profile }} and {{ data_form_link =\r\n| subscription | Subscription control }}</div>\r\n</body></html>\r\n\r\n--=_934102e9f090e995ad43d8dbc1f470c7\r\nContent-Transfer-Encoding: base64\r\nContent-ID: <1428640145552751913aac7833436131@daemon-notes.com>\r\nContent-Type: image/png;\r\n name=freebsd-logo.png\r\nContent-Disposition: inline;\r\n filename=freebsd-logo.png;\r\n size=31094\r\n\r\niVBORw0KGgoAAAANSUhEUgAAAK8AAACvCAYAAACLko51AAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A\r\n/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9oEGQ8MG4ps6uEAAAAhaVRYdENv\r\nbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBUaGUgR0lNUG2wlwAAACAASURBVHja7L15kCTXfd/5fUdm\r\n1t1398z0zPRcuK/BACI4FAUSJEUIIqiLVFCWl6SkXYV10FodQUu7kjakpYNSeGPXtlYStVpzqbVI\r\n26Ioiros04EFwQMESJAAAcwAGMw9PUffR115vfd++0e+zMrKrurpISApvN6OeJFZR1dlVX3ym9/3\r\n+/3eewyv7Y/d4PMJ///ffy1/f+dssNfhoNhrODj6r+wH+v/ySc1ew/dD3873wV4jsGyHr0MD9um/\r\nkB+Q/R0DTP8Fg309Lm6EjRvmQt7AAea3+bYdxEVoaQcH+w/5g7Gd3P/Wt76V52+3Wq2+x4MgGPg6\r\npVKp73PV6/W+21NTU/Snf/qn9DoA//f1HQ1jo7i/HRs0YD9/mw37vOwGwE0bH7Jf/CCDDmi7Ngxo\r\n+ocCNQ9pCmgKZrrVWjMAmJiYyJ4bRdHA13Zdl/L7165dIyEE5cHOA56H+4knnjD/gCc724aJQYLG\r\nriNyRTbMkH3aTuTYDUCbwpo2kdtPHyu+Jg04KFPY3+kB098BsH0/ylvf+lY2CNLp6WmeQpmC2Wg0\r\nuFKKxXHMAMAYw5RSDAAqlQof9OZCCGq1WiSlJM45AYDjOCSlJCklra2tUQq467rkeR7Nz89TqVSi\r\nFOoU6G1gpr9HYAcJWZEJNkCFi1wMa8NE7rrwFqEVua20WzEA4uJB5gHVuQPTN3jA9G38ONt6svvu\r\nu48PAjWF1BjDarUaj+OYGWNYuVzmWmumlGKe53GtNTPGMGMMc12XM8ZgjMlgzr9xCivnnIgInHOK\r\noshwzkkIQUIIiqLICCGIc06+75sU7GazaVzXpRTo5eVlk6r0dWCm1xlaNgDMQWKWv51/LgYort6m\r\nmW2EbWgPkQ04sBRaCcDJbdP7+ZCDNEMOUg04UD1EmW9UjQf+AMeOHeOMMQRBwIIgYFprNjExwQeB\r\n6rquUEoxKaUwxjAppWCMQQghjDGMc57ez7XWnHPOATBrIxgRMcdxsoNQShGA1CaQEIKMMZpzTkop\r\nI6U06TYIArL3ayEEaa21EILCMNQp0EEQ6DzMpVKJarUapd75M5/5DN0gyGwHV91BsIrrtO24yLMQ\r\nF7aqwEb6P9lnGaZM6ZvloXVscwvNyUGdV9/0y0vfXOUOdNBBquuceeZ6HmgYsKmqdrtdnofV8zxR\r\nBFUpxV3X5a7rcqUU55yLtGmtuRCCM8YEAEFEPN0KIZgxhvNEWhkR9X23jDFKGxFpxhhprQ3n3BCR\r\nllIaY4xRShljjBZCGADaGKONMVpKaaIoMkop7bqu0Vpr13VNEASmVCqZdrttarWaKZfLtLS0ZOr1\r\nOk1OThIA/Nmf/Zm5wY75IGAHwSoLopZveVETQ7hIf//I8hDa/WgAH0UVHnqJSM+WvNJ6tpUKWy8H\r\ndvEgTQ7cPLSD2rCzTg+wFeZ6ynvvvffyMAxZp9PhSik2MjIilFKsUqmIOI6567pCCCGUUtzzPC6E\r\nEAAkY0xwzoUQQlhIJRFJIQRPH2eMCSIS9rmciDgRCSEEN8ZwAHwQvNY6aMaYISJDRNrCayzQyhiT\r\n7SulDOdcEZHWWuuiAERRZIhIua5rlFJaKaU9zzNRFOmiIk9OTtJnP/vZ4onPhvz2g666YgCoTuEq\r\n7Fznqpz+mQK4oW1BroUFiLcoMNvGLqQH6OZgLdtWsa1cAFgOOEg9ANxoSIsHnHVqBz4o+wy33XYb\r\nD4KAGWNYvV4XYRjySqUifN8XrusKKaX0PI8zxiRjTBKRTGEFIIUQDudcAJBSSqm1dqSUkoiklDJT\r\nFMYY55zLdN8qMLMQMyJiqe/lnOdV1xCRAWAYYxqAtvfFRKRNIt9Ka60AKK214pzHKrkMxESklVIx\r\nESkASkoZA1AW9gxypZSuVqum2Wya0dFRs7S0ZACgWq3SV77yFbqOTeRDgHUKLX/1ze87hSvyIFHL\r\ngxsA6ALw7TbdD4YAPBTefKcsBTcFtmpbzbbKWxjuup3hNsEgJMAdBuYwsAjwTxssfM3gygKhXQA3\r\nLJxxxdt5mAeB3KfEBw4c6Ovxj4yMiDAMealUkq7rCiJyXNdNoXOMMU6pVJJE5EgpnRRaxpgjk+u3\r\nY29LIYQDwOGcS8aYtM+VAISFV9h9kXpfxlimvJxzGGNS25AH1xhjNGNMWXiVMUYDUEQUW6hjY4wy\r\nxsR2P2aMxUQU2fuyE56IlFIq5pzHnHOllMpEQyml2+22cRwn6+wRJfxeuHABhd99mMK6A5pX2Bbt\r\npPOQwN59DONzDLtjAosBUgRSgF4mLD9ncPI0cBlAB0Dbto5t3QEAp4K45bKRtwtOAdwagDqABoDG\r\ne4Dvu5fhXSNlr9wYG0O1VkXZdeBxDo8zuCDw1RU4fgernc7SImHxKxqv/EWMM1cIm4VLRVi4XITX\r\nAVkD0OPj48QYIwAYHR1lxhhWqVREFEVc2j8hhHRd19Vau47juIwxRwjhcs7T5nDOHSGEm4Ka7tvn\r\nOgAcxpiTh9cqt7AqLUZHR8u33nrrpIW2GHUBEYExZqx1ME8//fTV9HMQkU7hJSJlW5xutdYpoLHW\r\nOjLGRIyxSGsdAgjt7VApFTHGIsZYbIyJpJRxu93OAI7jWEspaWNjgwBgdXWVtNZca803NzfZEEuQ\r\nB7XYSsX7HpHY/y6JO27imNvDsD8eGUMkBEJiCJRCQEAQJ1tfKfixxulYff0/aPPJNWARQBNAy7Z2\r\nQYHztmmLSS+qbh7cBoARAKM/A/zMFGNvHBkbxcjEOKqVMiq1Kqr1OsrVKiojDZTqdTicQWgNvrEO\r\n0dwAv3wJzsIVnNxonvmywit/GOHEJYONHLh+YT8s+J8M4kqlYjjnulargTFG5XKZua4rHMcRnucJ\r\nAI7W2hVCeI7juEIID4DHGPM45yUhhOs4jktErpTS45w7UkrX2oYM7BT20dHRyq233jp94MCByXK5\r\nXDp48OA0ALZ3796pLYabDc/9pIqX/oVhGC0vL2+GYRheu3ZtbW1trbm0tNR8+umnLwOIjTFpi4wx\r\nERGFSqnIQhsaYwILbsgYC7XWmQAQUaS1juI4joUQcRiGRmutu90udTod6nQ6ICLWarW4MUb6vi+2\r\nATbf18k37/sdzH3QxbF7OG5pHDzUUJPTUI0RxEIi9APEYAiVgt/poL22hnazBT8I0fEDdGOFQBv4\r\nhvw/AX7zDHAWwEYO4naOhdjCGw+CN71kODmPmwd37PuBt7wZ+KmwVkOlXEKl5KHiuSh7HsrlEqoj\r\nDYzMzaG2aze8Rh2u60FwBt5ugXfbkMZAbm5AvvoS5NlX8cWNjZN/q/Dyv2jjeXuQ+VYEOnQcJ2aM\r\nxYyx2HVdU61WyfM8eJ7HHMfhnHPpOI7rOI4LoCSE8DjnnhCizDkvM8ZKFtaS4zieVVmPc+5KKV3G\r\nmCuldB544IG9Bw4cmDp06NCu6enp0UajUS2Cmd8vQjkI4OJzht2f3l5ZWdlYWlpaP3PmzMLzzz9/\r\neX5+fk1rHRJRCm5ojAmIKFBK+UQUEFGgtQ6IKIjjOOCchwDCKIpirXUchqGK49h0Oh34vo8gCHi3\r\n2+VhGEoicojIVUq5OVjLOVDT/fIbXcz8XAX3v5Pj7rGbb2+oQzdB7d2POFaI2m0ox4X2SlCcIfRD\r\nBO0WOkvLWD1zBpvXrqEbRIkKExBSYoRj4Mw/B/45gHXbUoA79vfPd/CHRhjyqlsHMApgDMD4vwQ+\r\nAsb2tF0Xruei4rkWYA9lz0W55KE+M4ORQ4dQO3AQpfEJSM+FYIBQCqy5Cd7cgAwDSK8EtngZOPU8\r\nVk58s/mfY7z4K6v48rzCes68+wB8IUQGsOu6seM4ked5xnVd47oupJTc8zzpuq5kjHmu65Y8z/MA\r\nVCy0ZcdxykKIshCixBgrC849N+nFuXNzc2PHjx8/cPjw4ZkjR47sSeFjjG2rpANDHWwQoDtT4kH3\r\np/utVqs7Pz+/9MILL1x89tlnL21sbLSMMYHWOjDG+MaYAEA3iqJAa91ljPnGmMAYE8RxHIZhGMVx\r\nrHzf10EQmCAIWBiGPIoiEYahjOPYi+M4BbestfZynfQygPI/G8c9P1zG0fv37T+CBx4CDtwCow1U\r\nGCbX9FIZplqDMgStFOIwRNhsoTl/CWsvncT6ufNobTYRGOqDV1jAPgV89CngeQBrOQVO1Tffqe/z\r\nZjynum5BdccAjN8J3PzLwP+8AWCDMTDXSeD1PFRKVn09D5VaFaMHD6J+8CCq++dQnt0LWS6DE4FH\r\nIVjgg7dbEGsr4IEPVDyAGeDUc8BzX8QTi2snfmURj3+tg2sAuhbcLuc8YIz5UspIShmXSiXjOI4p\r\nlUpwXZeXy2VHSulIKT3HcSpCiJIQompVt+I4ToVzXpZClKSUpbkDB8aOHz8+d+zYsbmZmZlGCipj\r\nLPWo24LJ0q+OvfaisxTQ4na7x8+dO3f15MmTl5588smzm5ubm0opP45jnzHWjeO4a4zxlVK+MaYb\r\nx3EUx3EQhmGslNKdTscEQUDdbpfHcez4vi+VUq5SygNQNsaUiahijCntczH205O4+ydH8ObJ73io\r\ngePvBMamgTAGGYIhQLslULkMzXjSozIEFUUIlpfQPn8Om2fOYP3sGWxcuYpuGMJXJgM3oEQlRwG8\r\nCnz+fwP+HYAVq74bFt5uzjqE+aqyYR23fILCPQDsymIfRAijOKfjBAcERYQYhO6Vy3CrVXiNEehG\r\nA7LRAB8ZBScD5vtg3TYwNgbqdMA2V4GoCxx9E3D8Ibz1lW/e+fTLT9/5jTOvnvnDZTz18UU6xTnv\r\nMsYCKWXAOQ8cx1FCCOW6rpFSQkopXdeVQgjPdd2SEKJsAa4IISqOlFUhRHl8fLz+zocfvun+++/f\r\nnwd2kCXo3WQYLL6vVzkvBh7DIJDTEwsADh8+vOfQoUN73v3ud7/xpZdeuvjiiy9efOKJJ141xvhC\r\nCF8p1eGcd40xmfWy3lkLIbQQghzH4Vpr6TiOa4xxpZSe1roCoLTPo9GP7sebf2Cmdl/lwR/08J3v\r\nArgDpH3SSAHCAcplMC4ALsA5B4hAvg9z7SpUs4locxPB6grCjQ0YxkBcwMD0Yl65XuIh4KZCqG1o\r\n+YHcJsNSjPc5s8BUPngXECGOIsSgrEkCHBDCtTV0zp2B47qQlTJkowFnehpy736Q7wOrK0BzA2i3\r\ngEYdiENgbREIAuC2+4B734T7F88fuf/pvzzyi6dfufwvr+GLf7wkTqfKyzmPpJRaCGEcx2GO46SR\r\nhVISyvWqUsqyFKLiOE7ljjvv3P3d3/3dN99zzz2znHNwzrdRVqup7PWH9EaBzl8BiCi7Xbzv9ttv\r\nn7vtttvmHnnkkftPnDhx/m/+5m+eX11dbSqlulEU+QBSTxxprWPXdbVSiqSUXEoplVKOlNKN47i0\r\nv8JGf3O/Of4Dh3cfqzzyEx5uewDwasn3QAwIwwRatwQwDiYEmJSAkOBEMK0m9OoKVKuFaHUF/vwl\r\n+IuL0EQgIWAQwxASxab+8FaUfNlOITs3qG5mYD3vsAiE2AVM5GMoHMkBqDjO4HWsCksA4dIS2gRI\r\nziCEhChXIGf3QUzPgCYmgJUVYGkBaG0C3TZQKScQbywDYQDsOgD8o1/GrYtn9/4fX/vcP/6FM69c\r\n+Z0r/Ct/uizPua4bc8614zjkOA6zCQjX8zzXdd2yI2XZcZzKGx54YO+jjz5629zc3FgK7SBg+0H+\r\nu4fVhAEoDEFK9QfaXQ8QHEw6YI6zRZFTWFP1zdscIkKj0agcP378jje+8Y13vPzyyxc/97nPfevS\r\npUsrYRgGRBQACJVSsVJKSynJcRymlJKu6zq7HFX/H/fHb/j+I9P3VB75Jx7ufwRglh2lk9+ECaA2\r\nAjAO4gKQDuC64OUywCXU8hJMtwu1vo7w8jzap19F98oVGG1AXICYhorjvkC90x+jKyZL2LAfRN5I\r\nkcYSsFHM/SWBSkDFKrMQ0lqIGIRweQntFxS4isGNAgehdP8D4COjwMwuULUGXLsMNL0EYCmBxgjg\r\nt4CNpeQL230Y+JFfxa2LZ2d//xufe9/PnXv12kfPe09+vVNachwHnucxz/NEuVx2HcdxPc8rvfvR\r\nRw+/8+GHbx4bGysLIbaobD+wf3/KasIA0dWrIKW25ubZgB+gXE5A9jwwzwMvV7ZYizzINlnTp8Yn\r\nTpyY/+xnP/vixYsX122ITRljFBHBGINxHpZ/7tDm0UcPjd9R+Z5fdvEd3w8ImYAaRUkTAhidtJcm\r\nDggB5rigag2sUgETEnptFWp1GdHVK/DPnkH75ZfgX7kMQwQjBEhrRL4PTf1pUqdQXzAE2i0/khhS\r\n+igKaeEKgOosMPkAcG9c6PYJljROBpxMoYKDwOIItL4OFgbgcQx02hDlCli9AeZ5QKUCKNU7PKOB\r\nUgWYmAY4B7otgDNgdBq45+2YvPme+g+Ord5+T2lt+kJcUV1e9jzPa1Qqlfqb3vSmuV/4+Z9/4Pib\r\n3rS/Vqs5RXAZY+CcIcnosr93SxAuXIOJoh2XyZBSoCgEdbvJ5XhtFcbvJt8XAO649jPxPiXO3zc1\r\nNTXy4IMP3nTw4MHJa9euqVar5RJRhYgqPztz6ehv3dp629Hv+2/3Oj/1fwocPJrYATBA6yRMUq4C\r\npWoCtHQBrwTUasDoGFipDMYYzPoaolMvIzp3Fv6pV9B+6QS6164i1hqxIShD8Ftt+J0uFAExAcqG\r\nyMo2bVtJsg8bnwOeymXY8jHeviSF3KYqf0sh+SbQLSa3mbUOWemY1ojDKFNfZVsMgv/SSbBWE2hu\r\nAutrcG+6FeLITWD1EbBqLfmyOAMCkdgHg6RXOzENrF0FVATEAth1E/ADv4bvuvDM3He9/J/mPndB\r\nXXyKH1r/wD/5p4eOHDnSkFIWgM13ul7LeNFv8y/3Urxchu52rw/uNm9P3S5Mt9vrXVv149UamOP0\r\nWYvU2xtjcNddd+2566679jz22GPzS3/9v3Z/UD81N/7OnyvhwZ8EKuPJi2mVnBjGJBALJ9kHS4RE\r\nyERYPA9gDKQ1zLWriM+dRnT+HIJTr6B75lVEa2swSDpnxDXiIECwudlX96pz4a20LQILOylEL8JL\r\nQ4qFs3rcJ4CrH8nF0vLWITsoSgBWEVkLkdgIBSAmQnjxAnirCba2AqyswLkyDzmzC6hUwByZfClE\r\nCXE6SoCWHjB7ExB2gfVrgIoBJYEjx0F3vB3f21ye+56b3jInHafP026Fll4/KGknr7P1RHHHJ0BR\r\nDNVqDj+S4gOMkq8EZD8G61eZdivp+GIRrFQCL1fAGw0Ir5RZiFSJzco5fO+5/2WfuKcOfN/XgfED\r\nvaud1gmonAPVkeQzGgNwAbAU3DLgOElEodWEvnwJev4S4osXEJ55FcHleahOJ6GPcxjOoaIY3eXl\r\nLeCaQkmiJyVWldq4zuCELfBS4dfdAm4q16vAUgmYzp8t/oCDUtpARVGmupJSFQbi1VUEQQC2ugK2\r\ndA1s737IqWmgVk3gnZhIOgIAQBrQJmmVBlAfAzprQNCCcuuIxubgzN4NV4hvE1q6Qd9Lr5l/b9cu\r\n8EoZ4eJi31Gw3ImRHy7AKH0OA6PcZ6HCpyCA+T6M74Otr4G5Lni1Bt5ogOku8Nhvwjn9BeCR3wYO\r\nP5iYO9I9e0AEuOXE32ptjaW0HQSRQMsAtFugtRWYxUXohauIL11EePEC4tVV6ChKThhmwQ1DtC5d\r\nhNYm8bo2wmCoF1ZwAXieB5cxvKrUlZ2OpJDF723I0J2sQuk8cPEeC2+aAA8K1iEPcBxFVnnJloUR\r\nFAMEY4gvz4M1N8EWF8Bn94JPT4M16sDKArBnFqjVAeIAmUQBiJJe7vgsAlkGq43Cy/XI++OzO4V2\r\nJxDTDtU21wHMQUYDMhyyMQLuevCvXQXF8VaAhxwmMQbVauHiv//3uPrX/xHB4iIY52CcA4Jj/N5j\r\nqN90E8aO3oOpN70JJoogTnwG8pnfBnvgQ8DPfa13fGR60HIJOKWe2jLe60Uy2zfotIFOG9RsgtZW\r\noReuQV2ZR7y8BN3tQqsYRmmYFNwgQPPMmQTcAhs617FyOUd1dBRydRV/Dpy5TgnsUNtAA8YX9RWS\r\nPw+c/w4pv8NVaqh16GuGoGwyQwJQLGkyhbjVglIx+MY63OUpYGoaGBsB1paA3XuA/YeSM8N+yUq4\r\niMpjcMoVCCEyIPpCSkQ9ubohL0uDEaLr2A1KVPG671GAn3seKvv2I1hcgOp0BgKc2d/EN6D1yik8\r\n+4u/hODagoWWJf/DAGYY1r71Lay/+CLmP/c5uPUK7nu3wMTdh4Gf+TpQHrcf0bKQHk/aQTP2fpbr\r\nyBIlKtxuAp0O0GqBVleglxahV5ahW02YOIZRKmlgMJwj7nax+fJLUFoPHZyW1lFW6nWUajVcXly8\r\nNKAMdijAcsivZwaMN4sBxJ8Hzvys58E1Bo4xmfp2hwxG0wCUTRXGDJDMqi8YBBgkY9Ccw4gu9OIS\r\nRLcD1mwAIyNAeyNR4Xu+A3BdhJVR6JFJeJ6XKE2f2rKeigyUMboBi0D9wG2juDRwcOwNJCKEQHnP\r\nLKL1dQQry2CpZWBsK/AEvPDr/xO685cBlliI5Fs0IMbAWe8kL/Em7nlYYuKD/wK46d02Xpu7qBIl\r\nXpa7PRUunjJEQBQCmxtAtwM0m6C1NdD6GqjVhIkiUKxgYgWtYmhDMJwhbnew8cLzUEr32YT8lqU1\r\nCKUSqhMTkErhW8CpQvFNnkG6HrwYorxZIfk1oLmi9VJtdHS6s7aWwevbeO/AYaAM0GA2mZG8qeYc\r\nmjNowWEEh44lhJQQYQR0uknUgTRABnTym+i+6/0QjVGUXHe42g5kMPd52UCHWfiHHDRkdtBnY69L\r\ndMIdHYUol9C5fLlnkQp/S48/jo2TLyUfhfPke7CwsjT7ZhRmD63j7l/9KTgP/TrgJQmFnkWwHT7h\r\nWkaL/pklPz0R0GklWdBuF2g2E3jbbVAUwSgFUgraNqOTEyhutbHxzWeglNp2WLBMSv5QHRtDdXIS\r\n4sIFfC0ph7ze+LU0DkzDbIMZAG82XOcrQfDCD+3d+45SpwM3DDP7EF9nHLMmBq0UFGdQnENyDiUE\r\npCPtl6AhjIbQvdNUT+yC//D74I1NbLEJPbU1W3xnErg3/RleYgWAB0UFqOf7hkKenhf8dQ2rCddD\r\nfe4AuteuQfn+lsdXvv4MTAqrVV6eg7zkBrjvh13MfOg/AIffjcxP9MXqeAIzaAC4qU0wQHMdaDUT\r\nxe12AN8HxTFIKxidWAStclsA4eoq1p/+KuJYIf0Js1aQUA9AtdFAbXIS5UYDa0tLzS8AF4cMPNjW\r\nNlBBQgZ53hTe8K+AE+8z5h21qSl0rlyBSwTXPqhzCqxSlWUMiuw+GJQyiEUMoQRkLKGUgtAaxmgY\r\nQ+CGwAwQ3Xkfond+P8pj4wmoBXApVRPGcogRyFCi2iz1g8x+QsrCTFstRfq/pnAyDPOvIqfsrzn8\r\n2wdXZXYW/vIyoo2Nvudsnjq1peOXPj453cED//3tKP/E44A7kjxii2R6ry1yal28OlkvrCJgYw3w\r\nO0kNQxwnqWGlAaVASiedsj5wCf7FC1h/+ikoGjK/QQ5kAaBSqaA+MYHa5CR4q4WXEssQDRg9rIdF\r\nHPgQy1BU3zhXiha+ACxeWFubr83OolKvZ6X2YpB1YMzus8Q+MJbYBUPQSkMZnfRGjck+nAEQHnsA\r\n6gd+tA9cZseEJd+zzilH4hOJCKRiwMQ5ZUnTp1T4obaiREYBRtlLbL5RfzP2IIv320Y7bNimlScn\r\nUZ6e7n8uT9KyEMIqKAMxhsldPr7zo9+L8k8/1wMXPNfRZD1wh509RAmom+uJz9Um6ajZRrZlHTOl\r\nko6a1ug8/zw2n34qKROgfvEadAX2pER9fBy1yUlUpqagz5zBx4EnC0O/YgyeuyE7Yr6NKAyzDQGA\r\n8PGNjedL9Trqu3ejJGWWSzYFcJW1CwaAZhwKDAYcmonkOSm4hrL/8X/oH0H9yI+hVCr1wGUMjHP7\r\nQw7wolqB4sj6xZyi2C0VbufhBigpkNFqMKwFmInYtw3ljTS3Xkdt3z6AscQuMJvl4hwkBIhzCBg8\r\n8N9V4Tz6iZzu8FzULrUJbHuHE4dAezNJVhhrHYyNr1vV7YGbRBe01mh+6Qm0nn9uyzBvVbAN6W3O\r\nGOojI2hY1fUcB4urq8svAku5sWrXU14UlXeY6moMGF//J8CLwdWrYW12FrWJiSzswQccuGZJB80w\r\nBsMFtBAwUkBLB1qI5AgZgwZD9BM/DfHwo6hUKmn1TMEmmC1F2hRFoCgGjIV0CwjIFDGB1vTAJcDE\r\nGqTygOqhjcgAhr0ucO5EnYXrorZvH7jjoHbLLYCUYFImHTbOsfcOhcq9P5AoLtk0bh+4O4isBN2k\r\nKMqYXNOZ+uZVlyzAqtPG+p/8O3RfPdUP6QDFzd+uVCoYmZhAfXIS1akpxKdO4fPA1wuDb4fB2++w\r\ntok25MfX521DAMC/Bqw/f+7c6eru3WjMzqJaqWQAawDKqq5hLLMOinFoxm2kQcIICeO4MI4LzTno\r\nZ34e5Xd8D0qlUi+olZYC5m1CeqDGJL4szQYhr7J5K9tTWjL9ymoiBcT2q91iFwY0g8KPPLzRddpO\r\nX4cLgdrsLEbvvDNRXau8EAKjuwiozuVCejZstxNwyYIbhQVLZHq2QWnrc3uqG1+9gvV/8weIVpb7\r\nkgCq4G37bAQB3HHQyIFbmZ5G+8SJ8E+BF3JjFUMMn2hkW8/bV703xDpkw9T/OAi+yhYWUNuzB41d\r\nu1DiHJ4FrncmMut3k6yLESKBVkoYx0ngdT04v/ArqH3Po3BtYUnWMUs7EsXvPY6BwLdRga2p26TT\r\nNkCFcx7YRCq5JBIlSkM7aJrtWD1fLxVOowv7H3kEowcPojw9DVEqJd6XAMy+JfmO0k7tTsbckQFC\r\nPynCyUNrej43AbentqQUgue+ic1P/hHiINx6hS2A3KfAnKE2Opqo7tQUqlNTUGfO4OUgOL2QDPfZ\r\nyUw5tF09Lw0pzMnDmw1Nfxy4cPb8+fnDb3/7kLb7wwAAIABJREFUvsbGBlobG/DX1+HaJxvGEuXl\r\nqdqKZCskjHRgZKK6lV/6H9B4+F1whAAZA+7I3mgGMluKrCgMks4FY0k8uFieYUNERDQk0MVgYgWo\r\nHPhcJXBuEwogMHsemdc3wrDD55dmZ7H3bW+DbrfBSyVorTFSfQpY+Baw7y1Jp2wnVWrGJHXSRvf7\r\n+kxxU9VVIJ00026h+xd/htZTTyLWZgukapBdtMqrADjVGhqTk6hNTaFmVXfzU5/C7wCPF1R36Aw5\r\nO7ENwzJtReX1AfifOXfua6LTQX3PHozu2YOK58Gz4Zy0g9ZTXgGSDsixquu6qLz3R9D47u+BJyVg\r\nDJgUfeAWI7HG74LiqD8Llea5KM13JUF7NkzllAbFuv8STtp2VvRwFdav3TJc9/mkkEyYk9g/VmgT\r\nDx6HqFYhqlWUp6bARg8D3Y3h4BZPX6OBONoK7RZwrerGCuryPDqf+EN0v/50UnCVQllsQ5SXXA+V\r\niYk+y4CVFVxYW5t/EbiG/qkO8rZh28Kc68FbjDrkIw4+gO7HgW+tnT7drOzejfrsLEamplBiDK71\r\nuUknLbULtrMmErtQe+RRTP/Uz6LiuiBjwBwHnHELXbFjZkDddlIOia3zIlBfJKwQ1sqDqw0oUrnH\r\nLTykQEZv34bAe6N+tg9UhADrgrE2OG+CszY464DBH9jG7r8DslaDrFQgKhWo8iHgzBPXzQZmitsX\r\nUTFbPa6t503tQvzVL8H/+McQXjgPFcfbgjoI6JhxOKOjGJ2cRGNqCrXpaVSmp9H5/OfxKeAJ9M9L\r\nlldevZ1l2K7Dhutk2vKz2vifeuaZL7tSor57N0ZnZ9FoNFCyoS1tO2m9KIMD4zjwbrkNe37xw6hK\r\nCdIaTMokgzbgOMkYmHYTpGKrrGz4yAMQiKmk5ck2NnkR6T5o031GcU9hzZCm2UBYd67AGkAIzjrg\r\nrAnOWuDoglGYvL/R2zYyGk6jitH77srUVzduhrrwchKf3hbcQukjGRtUz3nctBA9VqDlZUT/1x8g\r\n/MvPIl5fRxwE/dAWINUDkhMRAFGvozE5iZHJyczr0uIirrzyyvJngZeHqG5eeYcWo293rRk0X2t+\r\nIraspPdpYP0fA/c17rzTM1EEFQQIWq3k9BECQkpIR0K4DhzPQ2X/Ptz+u7+P0Xo9AUdwyErFWoVB\r\n4G4mNoBxG5xPT73cYfZNqBAD3GRZsKx0W5uhHSbGg75OXV9LO07a21FHrO85MACLwVgAzn2wTFTM\r\nDXXmQARjJIwugZdqCBcWIMpliHIZMl6HWxLAnjsHV8RlJaVpxKQAq1JJHyKOgWYT5onHoD79KUSX\r\nLiBqtxF2u4i1QWSH78Q0eG7a9LGIgNAAVCqhPDODyd27MWFtZWN2Fq1/+2/xZ8vLjz8NvApgE72J\r\nRbo5iFUho7ylyyB2OBxg0Bi3bApUxph70+XL40ff8pb9cF2YOEbU7SLyfcRCgEsJKR1I14H0PBz9\r\nV/87pvbvB9caRASn3rApXNP3pRMRTGvd1pcyMG5jmMyOQWP91VxJPNiAcd1TaLLzCJiehdjihY0B\r\n490hobF0K0DG2RmwRAAUGPPBWBccIRgNyNyhmPwwAxIiBDISWpdB2kmyvpVKMvKYCLJSgRk7jMqz\r\nHwMe+G+Suty+K47JCpx6nbIB4LZbwPPPgf7kk9DPfQPx5ibidhtRt4O4b36OflDjAtCRnUhECQF3\r\nchKju3djYvdujO/Zg5HZWfDlZVz99KebP83Ypznnm0SUn9IpVeBihw03Mnp4WOdNDeu4/WvGvvy9\r\nX/zi/bWHH/bqzSbGmk34vp9UktmkhJEODn/oQ5i+/XbwKOl0iVo1Cbin4OaU12yu9kr3+gq6WS+2\r\nkKu8SYIMOneOajCyWblcfDeN9VIe3vxld1CkwbiFsNywyEEIxsNEYbcpA95J1MGYEowp2+5oOpl4\r\n8lc5cgSdU6fAXRfMG0d0QsK9fBKYO9o7oY3u75Cl4OaVd/Eq8Nw3gGeeAi0tQ282oXwfKhWfMMoG\r\nS6ocqHkLkalvCi4A2WigPDm5xeuuffzj+CvGvsQ57xKRP8DvblvPsFPlHbZGQd9sOpxzlzHmtBnj\r\nBy5fbhx96KF9zKqvjiKEnQ60lGCOi71vewj3/sIvoaR1FhJz6o1+xU2Hb7fWk86ZzeEnqtur6k/q\r\neZNRE6nKJqOyVX+ommS/Xcj53L4t64LR8Es5qXJfPQPlfbMxAHww1gRHYFV2G4XG9jbBaBdKVZMT\r\nJp8dzCdYOAeTEpxziHIZbGQPnK/8G+DeR5PvbfVKb0RE3vMuXQGuXgJOvQh84T8CX/0ScPY0sLEB\r\nanWgOm3E7Q6iTju5emrKrEC0jfpGBATGzoBXrsCbmcG4tQtjs7MY2bMHbGEBlz/5yeY/dZzPENEm\r\ngCbnvEVExbl4r6u611Pe4ri2QVVmIWMsSNvvAE++/QtfuGv6Xe+q19ttBN0u/CCA6XTg7N2L+3/1\r\n11DlHCYIkvFLjcmthd8WXAr95AfKThu2pY63b7IDxgCm+mtSGYFI9+pd88Aa07vPGJBJsnpcNgfU\r\nows72FoPWAwsBOMdMJjcYgO0Y4Xtr40R0KYGIsc+orYdxMQbDagoQuvCBeiohPrT38T40T8H9t4B\r\nBJ0kiRP4gN9NanK7HTsaogO020Czldzf6YI6PpTfRdxNWuT7iJXpX2shp7qZAtvHQpNsSUo4tuhm\r\nZGoqU93y9DRWfvd38ddSftXOXBkA8I0x4TZZtW2HsIgd+N3iNlPe/MTLnHO3K4RTunRJfNfb336Y\r\nlUowcQzSGrHv49hHPoL9d9wBajYBIsh6HaJc2tJBM34bprPZK7bmoudvWZL2zB4D688qpfDm08LK\r\n1iIUwN16myeNta0Cm95lV0tAu4WOWATGNsFZG8zoXnbKBv4p/xrDIhE5b6t1GVrVAcMGdgqL9xkb\r\nwVh/8UWc+O3fxuKTT2Lz6iZmg5Pgx747SZuHYTJ9VhAAfgB0/QxWdLpAOwGZWh2odhuq00Hc7iDs\r\ndBCFYeZhU2XNfG1BfQNj72MMYnQM5V27+lS3PjsLXL2K05/4xOo/K5f/ynbQmkTUJKK2ECKw86kN\r\n87r07cALDF8lhgshsunw7YTNpW8K0X6fMXePHjvmpgUdEw8/jJve+17ITiebKcadmty6nmccwqwt\r\nZEUn4DwXYehtGcvl7rPHqDdmMEsVa9sn4v0Wwe5vCXMRgVEEhrCvd06qDsqKcbSFtmnDa/2gU18n\r\nb1CHrL/zRJpDqREY7W4JsWFIqpnsFYOMgbd7N5Tvo3XuHHy3AXn+JUyMSGDXoX5wfavAHavC7a4F\r\ntw3d7lhw29Yu+Fug7a0f0H9/YBKfGwNAtQpvZgYjKbi2k1abncXyr/86fsf3HzsrxFXGWJMxtgmg\r\nzTnv5hQ4vl5sdycZtp1m3VRywrF0LYRISqk++eSTz2FxEdWZGTTm5rDnh384swvQGs7Y2NYkg9HQ\r\ny/N9RecYsM9SYIVIKqyErbISAmAOwISNA9voAvSWyEI2P4Hpr1mF1qCoDNI9FaVYghSzNqMDzhbB\r\nqA3o2PbY42yEAdnb6f1QEaAikG3J7Tj5HxVDRy7icAQmRpYUyNcRUBz3EgZx8j5GqaTKK91qjdnv\r\n+z5U5uYAzvEKDmHz0/83sHg5KbjJ1NfvqW/Ht1ahC9P1obsFu2AoU9nMNhQsg7IgR3bfOA6c8Ymk\r\nkzY1hcb0dNZJ87/4RZw5e3b5sXJ5XkqpOOeKc64ZY4Nqdne8ZJm4AXj7VNfzPIlkxRyHMebZSZur\r\nQojyCc8LH2y39+/9ru+q0BvegOkjR6CWl21MV8Cbnt4SmzVLFwGt7FSZ3NoF0VfDmtiFBFqIBNxs\r\nQgyWL7buTaDBSIG0LPS2t0LbU2UkE5qQArQE6TqYicGxAs5aSTIgex3VC0WlvXdjABP3VF0PSjYY\r\nKDUCrctbrgA0IMmRJTtyJ58xJilTtN9pZW4OK88+ixgcGxsR9p1/EvyeNyeKW7QLrQ7Q7oDaiV2I\r\nO21E7Q6iTgdRLiwWFTpkecsQmMTnpnZBjo0ndmHPnkx1G7OzKNXrWPm1X8MfCPHMBSmX0L/eRIdz\r\nHmitA9d1Y6XUjjpqNwrvFs9rFytxGGNuOmW+TObArUopa5tra/yhu+/eX3roIZS1hm63kwLryUkI\r\nzy2ExJZA7c1EPe08rwm4vB9cOyshk04PctYfhQBM0klTvYwZKdkPrlL90G7xvwxQLqBdG0VYAkzY\r\n72utlyWtciD3Rh1kqVidKGSm7MogjsdgtOw9N1czm9/f0vLZOguusY/JahWiXsf6Sy+h5TUQzl/F\r\n7OZFYN/NVm27SWfN2gWTB7djowt+sCXZ0Odz7W3fgptCzup1eLt2oZGPLli70P7MZ/Clp55a+OTY\r\n2Ms2qtAG0CaiNCnhCyEiACm8O7IMO43zbpc+pnQuLCGSiYWTtfcYe9J11649+GBwdHS05J8/3wuN\r\nNRp9x0VBF3rlCph0+88V1osmZFZCSDDpDK9VTb1wX9GJBihKgCyqra2cSpMg+WFFgAHjGwDr2m40\r\n9Y35IpP7fk06FKm/so2QK8tkgDEOYpqy/xUNXY+WCpOMZNNfpbdT/0Zk35qgjcH4vfeiOT+Pxa9+\r\nFWfGj2Dkqy/g5pHdwH4LcDcAOj5Mtwvd7SDuiy4EA5MOKcypXfB1L5alABjXgzc+gfLEBEZthKFu\r\n7QIPQ7T+/M+x8mM/FvK//VueW3CR2bXqOAAopYYub/B6w8sqlQrTWkNKyTjnjHPOhBDc7nMhBH/f\r\n+963/+DBgyW1vg4TJzUJ7sxMP2hEUAtnt0DLsqHqOUVN54K9XpF1vjbX5ALyCn3AUqq+6fy4KSDG\r\npnPFGhhFoDjNzOUyf+ivFe6N3kirK1A4EQBNDmLTQDKD2wBIt9sWwE3LPQ0RKB0iBMBojd3vfCfC\r\nbhcrzz+PZ0dvwshf/CVmHnwImNkPtBOfq7s+Yt9PwPV9hL6PWJuB0OZjuL7JKTCQjAAfHYVnfe7I\r\n9HQGbmlmBku//Msw73oX3vKBD8z+p5dfPnX27NkVIspWFbXrNgshBNdas3q9zlqt1rBCztcMry0/\r\nICalFETEOeciXU3SrmkmJyYmyh/4wAdub9Tr6J4+nVSMcQ5nbKzPLuilS0lBtHR7Q7SpGL+1NQyp\r\nVbgOuES6z9cml3XRU16VS4tqDZaHFwBYG+DrYHZYEAOzKove+Lh8pi1VYEO54XG56fiJYOAiopFk\r\nwqvcPGTZtP0FUKmwXwQXA4YW6DQKoTV2PfQQ2gsLaC0t4f+ZvhNv/9KTmPlOCeIyi+VGuRZHcT+4\r\ntjYhsC1Er5OW78jxWh3u5CQaaSctl0mLT51CfPIkyr/3e9Dttnz/+99/20c+8pErxhhpw6spM0Jr\r\nzV3X5emi4zsBd6fwbon12mVKuRBCaK2lXRHdZYy5nHPvgx/84F2zs7OO3thIVJcIzlT/cmUU+old\r\nSGe+KS6FzPqHbLN0wrfrORoV2dkl7ShiY+GNrfLGcQJtWoiSA5HxNYA3gdj0wMzAzW1z49/IDBsv\r\nlz5dIqI6iML+LrRVzS0+zF4BBgGdjhbO4M2BntoIssOGDrznPTjzmc+gvQz85+mb8c4vPIHxu+9O\r\nIgmpXfB9hGGYRQ1SLxuYXkYtSiMN6E9KkFeCOzGBaqq6ObsgPQ+XP/xhsI9+NLOTx44d23fgwIGx\r\ns2fPrhljXM65a4xxjDEO51wqpYRdt5m93rYhOyOq1apQSgkhhCQiYRfaK9mzyR0fH6+94x3vuLnR\r\naGDzxIlE/QC4meomP0t84UQSMnK93syHeV9JaQ6C9SIK1xmPRToC4gCUhrF0nBReK4FkpV6VQJsW\r\no6R+FwaMLwPUTX6hPIRpvSsA0oWkgbUTRP3bXnyWw+840J3LiNfWYKIIJooQr69vUc78CMN0352Z\r\nATEGUatBVKuQY2PgntenzMbahgzmNHvIGPY/+ijO/eVfosMY/nbvrXjrc89jdHY3YqURdX0EXR9+\r\nTAh0EqvNUsA566Bo6wrnWgg4Y2MoTU5izPrc2vQ0qtPTKE1PY/OP/xh06BDo/vuhtUalUkGz2cT7\r\n3//+o7/xG79xGYBHRB4ATwjhGGOElFKEYcjr9TpvtVrs78I2cGMMl1IKY4zgnOfXmi0BKH3wgx88\r\nNjs760br69BpGnh0FDyddxeA3lyBaa6BuZ4VWMoupyw/epJynbDrdiM1EHcBFfTUN7ax1UgAMcvK\r\n/shumdYAKTC5AOiwB67J+dwsWZBTXVMA145aNovzMNcuwbQ3YdaWsLk2DoVyApftXGWgpZ2tQRBb\r\nRe2cP29nZ0ogJcbAPA/uxATcXbvgjI0lYUfXzbxv2ogInDHse/hhXPz859EG8NjcbXjT2RfRKJfQ\r\n6UbwQ52FwIpx3RgD/K/dF/VG4nPTFLCN6Zanp2EWFrD5sY+h83u/h1IUQQgBz/MgpcTRo0cPHDp0\r\naPLVV19dt4LnAkgVWDqOI+2C4XzARDg3DO+W7BoR8dS3CCHyU/97AErHjx+/qV6vo3v2bE91x8f7\r\npwh49dnCnAjUN20TpeWMbIejYI0GVAcI26A4tOCGNikQJnHbkMBScKMo8boUgMmrYJHuAzGFNLME\r\nOg+zVdUgAC1ehlmYT9rqEkj3/qdjdkExgqFOP7AFeM2AAYPEWG+bFirZeLfxfUTNJujixWQUMWOQ\r\n4+Pwdu1C9cAByNHRrA6ajAHnHHseegiXHn8cXcbw5SN3486zL2FExX0eNypCWgyRoVd049rhPGNp\r\nJ82OSfNmZnDpJ38SS+94K7ypKfgrK3BmZ/vU973vfe89v/Vbv3WZiMoASqkCG2N8IhI2AsFeL9vQ\r\nl5ywiiut3Gdr+gIov//97z968ODBGuIY0epq8s9C9MGrrp4D+W0wt1SwCdS/LU6zOWzqUVJA3AFF\r\nLVDkW1gttHHSKBSgMLEN2QIhugsmrwJRGtslMJOr+zWUzA6VVqRpAnVaoPmzMPNnYK5eTmDVBmT/\r\nj0wy9jtEFQGrwFC3TwlNUX0LKaU+mHMA55U3BTaddZw4RxwE6CwsYPXFFyFqNZRmZ1Hevx9iJJnd\r\nXHCO2Te/GVeefhodAM/efgxzp1/CTHsTSqM/KZHbFm2DFhKeLboZL/jc8swM1j/2MSwsX8H88f3Y\r\n5/twggDx5CSEELCrlOLYsWOHx8fHG0tLS+sWYM/WhEtjjDDGFJeuomEWQtwIuAAczrlnJb/COa8z\r\nxkY45yOMsbEPf/jD77rlllsqanUV0XpSRO5NTMCdmOhVu37riyAyduIM2Z9R48kSThACjMtkSad0\r\nxhdWXDfNADoExW0gboFCO/9AnPheRCEoChIP3PGAKAaCACwIgLgFhnkwFYHFybCXtLFYJYvj2S1t\r\nroNOn4R5+nGYZ74Mc+EcaHUjGU4U6AT+XDMR0NIzyXxeUZRMiRTHSdo3ncc2vZ1rVNj27af/V5hu\r\nidL70glBtIbyffjLy9g4exatCxdAxiSjVIRAdc8exL6PoN3G6tQMNgmotTeTqrAhitubvoZBjo2h\r\ntmsXJm1x+ejsbDJnx969oJUVXPrQh/Ct+x8AbzCUGiPwGpOgMIQ3OpqtjWH7S9EzzzxzCcmihqFd\r\nKzmyKxRp13WNUsq8Fs/LisU4nucJrXXaScuvBl5+4IEH9t1yyy0TrutibWEhyRYBcHKqG8+/CtNp\r\ngnml5HGZhLWYsIUq+RV6WE6RjQZMgN7iRcZ2ygJAWZ8bhkAUJbZBhUAUJMobBSC/AmYVl6ImOL8M\r\nFpvejIjppLGE5HYQgi6cAp17BbSynKhxWo1tVZlUT23JkK23IXT4BIjF/UVAyK2flu4PmaIo27e+\r\n1+RUN1PidOKR3ODWdDKS9DZxjiiOEXzzmzAnTqC0ezdqR45g6vbbIatVrJw6hc29czjRGMXM2VMo\r\nhSFiM8A+2H1UKyhNTGAsF11Iw2KO52H+J34CL91yCzYbDYxEIYKVU2jsvx3da9dQnZ2FEAKlUgnt\r\ndhsPPvjgXb//+7//VSLKbINVX8fa0h0t1XRDtsFKurThDTd9YyIq/9AP/dD9jUYDOgigmk0b4RLw\r\nJiezF4peeQZkNJjJgcNtTBa5VC/PlTuCktJPw5KSRaSx3DQkFgIqSOZyiCy4cQSKArAoAAWUTM8Z\r\nhkDUgmCXCuBaYDVAGyvAqycSaJUFVBlrD5KWTXmYeWBkE+8pOIgZh6Ggr/OUtwum4HfNEIj74C10\r\n2kxqHQr2Ib9vpOztxzGaZ89i4/x5lPftQ+3wYczcey8WT5yAGhnFlbvvgzd/EfVrV2CQXETy4BrH\r\nQcWCm/rcNLrgeB4u/+iP4kqngzN33w3XGASBA+WvIVi7Bsk9+IuLcPbvhzEG5XIZxpja2972tkOP\r\nPfbYGhGVLEMuEaV2NLZXerOdddgpvBwAT0Nj6dL29k1LRFS65557Do2MjKAzP5+V88mc6kbnX4Jp\r\nbwLSsTl8lcRu7ToTsCMjel7XHiu3a5YZZoP86RwKKqk30AEo7oICP6lnSBU3DEBRAOo6oG4XLGqC\r\n00Vb62DHsVn1xPI14OSzoMVryX2x6Q2JVbmpK7VVWk22f0m5UeSEDi/DINo6601ebbdR3WL0oQ/c\r\nFOhBEOehTe+zUw2kKpwqc/vcOWxevIjK7Cym77gDa+fPo9tsonvwMFq7ZyEvXYS7tAidC53JUgn1\r\ner3nc6emUJ2eRvjii1j9oz9CePo0nnjwQcAYaMYQxxwqNtCdC5Ajd8C/ehW1vXszeFutFt72trfd\r\n8dhjj52wqpuKoANA2CwcL1z5aafKO2zksCSibCFt2GXsf/zHf/zY9PS0CwD+lSuZZehT3XMvgrQC\r\nYzwZ6q4USOj88MneEJ90bI+Ie2+fnwTPKMCkkQRb7hd0rN+1XjcMwEIfplUHBT64uQAYlUzbmyrt\r\n8gLwynOgxQWw2CSpYKu40NYK6Jw9SO8rgktAyBxore2kIdsAOwBeGhDvNXl48+AWIU7tQwpzCrGF\r\nljiHTveFyGaXbF28iObVq6jMzaFeraJ57RrI8xDdfAu6+/aDLl0EFpcSyWu14J88iYWTJ6FnZtCK\r\nY3hraxgBwKTEXx87hlAIOMZkc9MFgUC4cQ5O7VYgDBGsrIBPT2ehs1tvvfWAMSYVP4+IXMaYY4yR\r\nA+B9TZ43U17GmMMYc4nINca4jDHvvvvuOzI+Pp6MGt7czIawu2NjSYRhbQHq2oUsS8aUBAnRi+mm\r\ncdysQgyAjHurVGax4LSIWyWWQYVA7IP8DsjvWI+bqC7CACYIYZolSDoPZqJ0hRfQxhrMiW8Aiwtg\r\n1stSZCy0SRc7BTWbi9fQ1ut8FusFfCZgoPrV9gbBHTTjSwZsGi4rWojUPgxRYspBnCoxy9uJV18F\r\nKhXU9u1D0G7Db7eBUgnq5lsQHzoMvboKubEBubmJWhhCLi7CBSCFwOnJSXzr4EEElQoEETQRhElW\r\n/ul2yoCOodpXIMQ4goUFVKanYYxBqVRCEATuBz7wgXs/8YlPPG6v4m5qHdC/0ru5UeUdFG3IK6/L\r\nOS8BKE1OTjbuvvvuQ5VKBd3LlzPLkM4pAADhia9lC0QzzhPfq3U2zKdvRIQgwInBbGF5dvzp4Mc0\r\nc6YikArAYh+m3U7gteCyMAAFPkyXgUdnQCZILv1BBHr5WeDCORsDMn1V1pROLGsIyUh16o1OT9WW\r\ntk7hG0HAMNPrmA0CdQC4201TVAQ4n31LFRkFgFEAWHOexNpTJVYKJGVyEtjJXkgI6DjGersNd2oK\r\n9ZkZtNfWYOIYUkqw6Wl0p6fRIsK53ImUHxzAjcmyChrJMmUGZURRE168CMPHEK2tQYUhOOdw3aSC\r\n8OjRozcR0VftFdyzoiiJSDiOI+M4VgUR7UtYyBsIk4nUMtheoQvAeeihhw6PjIwAAILV1cwypKoL\r\nANHZk4llyNKaHOSWsh+fpeN/pQZ3COBlQJYB7oGBQCbqzVieqW6QqG7UAXXboE4rid8GPigIkoKf\r\nbmRhJ5iLp0GnTgBBBBYVxnJbxSXVU1lS6bwg1Ded75YafwICMBAzfeAOH2RJWyzCUKgLKwJtAXpQ\r\nLNheurcosNaAXbyahEjqgK2NAOcgY9BdWEBndRUjR45Aao3u+jpMOv+bnbGT2RkrjenNSkmcJ7XF\r\nac/KdWEA+G2GsrwAd/owyDgIFhYg5+YghIDjOJibm9uTpoptP8rhnDta63yy4oZsw6DVtvOTjThW\r\n3h3GmPeGN7zhllGb1Yk2NnqdtXo9Afqlb0B32+BSZqWCTMgt78i9AMwpgXgVzKmDObVs+SWmOEiF\r\ntrwxTvbjAIi6QNgBbTZBnXaivEEA+F2wKAJTBrS5Af3iN4D1jcQixMXxLL0IAuV6TqR7liC/+hMV\r\nZk6NwWwmjbaAOTABMQDs7TpwQ1fQs4U9NKBDlypyOvIitRSwwMKYpBNni9rTDh0s1CsnTybp30OH\r\n0G210G02k0lOUtXNz76Zvoc9AYixZCIUpeC3BOJSBzK8AuPNIVxcRDUXdQiCwH3Pe95z26c//em1\r\nlCmrvHnrMLTTJncSZbAjhfOdtVR53TvvvPNQo9FI/O76eqYSSdE5EJ5+IRnflZMgyk2EzIQCr7TA\r\nRANgMgHXbeTqGeyyS2C2wxUnqht1gagD+E1QazOZhC+w8IYhoAjm/CnQmVeAyIApAsWFSbZiZOEv\r\nZno2AaZvPurezEz5RKBtIWUVvEM97Y7twaDa5AEDCPvmYLOQUm6bKa8xmY2A3TcpuBZUpNEIIXJr\r\nDXP4S0vwNzcxctNNqM/MIApDBL6PMAgQ5UDmOVvEPQ9uvQ5DBK0Ugi6DjhXU6mnI3fuhOh2obhe8\r\nVkO5XMbq6ipuv/32A0T0TBous4EAmWNvkJjSDdmGXBGxYIy5ANzjx4/vnZqa8jLVTWcoJ0Jpagq6\r\n00TwynNgUsIYA04GgJONfuXe/9vemwU5kqR3fn/3iEDgSgB533VkHV1Xb01fMz1DsucQh2yuuMPd\r\n5e5SJi1XJltb0qSHfZaZzKRHPellTWaN79OaAAAgAElEQVQ0k9nuakcUyRFltNGwOZypKW5Pd/V0\r\nF6tq6uqqrjOrMrMq60AiE2cCCESEux7CA/CIjEAEkFl9zEyaheGMSCDwwx//7/PPP69DSXVAyJjw\r\nu0nAA640T9RdqcZqA52WU8fQqoFVKmDVitOW3ugAHRO8VgW7dQ2oVEFEtp2bHMTiIKbwtib3rvYt\r\ndWAC36m04N6lLtzqyA7Cva1cexv0nL7tOeW6Xr/iSr6XyUXvMsjuZts9oN1Zx0IlXbsARXFsgLiv\r\nG9DZNoo3biC7sIDk+DgSojOlzhg6pul8EeCsM6FqmgMyY46FEJ3xOy1A2b6NxPRvgCEBs1KBlsk4\r\nrapUFUtLSwsCWF2KqRRf0Baovmrc0TXX8wq/qwFIvPHGG0sjwh60isWuZdCEjWhd+cApBhdrhnEA\r\nhCoghEPNbYIm8+BIgnAnE0cSWRCqBfVaAizDsQudJrixDbQbQLMKXt8Cq5aBtgFi2mCr98Ee3gMx\r\nOYgpfKwAl4vbxJagtXuSxmVw4avSDIDYlKxAoCWOCNL6Km+A6nK/6rpBnB9kuV7YLZl0bQSlPYvD\r\nmON7BbSuKvPuWsaOxaitrqLdaCAzN9f9QlJ5EUMATLQ0YN0lHAgoIWhtU2DrKXKv1MHNMZgbG+Bz\r\ncwCAVCqFycnJ8YmJiezz5883BVdaiG2InPpOQqyDv7meBkB76aWX9uWEPeiUy91Jgm6WoX37qpiy\r\n7a6YaIJoJhLjT0EVozszgUOsKq6mAivIuNUG72wLcBtAuwY0K+DbW+D1LcfzlkowL52Dff8uuMHB\r\nDQ5mMPA2E9c5eEcAbLqjZ1Lv6IDeenIbW3k1LHfrRFiAPjHecEAHPKf3zXJnbaALJ3xdgtwJm+4k\r\nT7cmYsf0KKkBHxHT7psbG9i8e9dpXyA9h0v7eWouTBO2acJocjCLw64uA4zB3Nzset5kMgnGGN56\r\n660lyY66jClRANOYqksleDX3cnp6ejKbzTrw1uvd6dgJkX2onvnLXg8CywIhTSTGn/dOuy1N8yYU\r\nIGqg6vJWGWjXwY2643G3K+DbZfB6Gdiuw157CPPyR2BbdQfStoC2LYAVQ0Xc5OAmdxIXlrfpudsc\r\nJ7B9WAhUnQAvOgi4iLg/zuNhIHM/xBLMTG66IiDm0hQpJs3x45YFImaemI0GSrdvO4t8u60FpBnZ\r\n3AcxsywH3g6HXXsIW3T6MctlJDTVHSrGyZMn90vQhoG7A+DYgxTC83bV9/jx4xOLi4u5brXY5mb3\r\nJ0TNZNC+fxN2reIoL+egahv6eAXgowAnju/kxKlzECeS+Lt7cw7ergKNDfBWDbxZAbbLwHYZvFEF\r\nb1Rh37kAa3lTeFthA0xHVYl7GbR+qD/68VEWNBFSvm4ivOf8sCqMIdQ59H43vSbNtOjpAfP4Yrj1\r\nF+46How5eWHhh932A+7nVLp3D9m5OegiOPNYGFEE754bwyJgNkfn2Qb02W0wNgJLrOypaSo0TcPc\r\n3NyUmyqT4KU+z+tnk6txgjXhdykhpJtxePnll2fT6bRTLdZoeNoTqZkMmh/8Zyepb5ngxEYiXxbF\r\nNVIll9yYwzKcmRBqsuuluFEDL6+Bb2864Dar4M060GyAbz0GX/578EoNvM260BILYpgXnrVkuQCX\r\ncLGKaxhNMWbzevwuED4jYkBw4466DQJyEMTE9cdSfri7wibnTvZBXBL3tuuDxRSj2qNHSI2OIjM5\r\n6TQ+kTy+OyTmDli0toH0sy2MJspgrSwskZXSdR26rmNxcXHeTZGJ/K4aUt9AorIN/VqadpV3aWlp\r\nLplMduFltt0d7uWcY/vyz0QtqgU91wJFC9xKgpvMKfC2GYjlNtowgU4LvOn249XAOw3wehHYLoE3\r\nq45daNbBGxXwJ3fAH38C1OvO8Qwng9BTWkBeBJczJxUm52vdT9e/jjYPG1zwXXZi2oW9VOFhQJYz\r\nHC7EXM5MSDljF2A3sCNCkYnUYpV3R0MJWpubsNttZKemnClK4v/IKTQGoFUx0GFtkEQdaHLYjQY4\r\nZ0gmktB1HfV6HceOHRu/devWhqu8nHOZPzJIbUNgV0hJ0tX5+fnpTCbjwFuvd9sREc6Rnp3F6uUP\r\nwBmDXd2COmuAdwAQtbvCDDEtcM106mxpGyDbYLwI0q4LQ9nqBme8WXWUt1IEe3QdfPMxUKsAhu2A\r\nKyut1VsQjAulDWzdxncE7ZEdVuRLo09BzYuCOeo+9Gle4p+d7B+1614XI2dcanjSzcu7veJEKwNO\r\nCMx6HbV2G9nJSVBN2/GFsSwLVofA3u5ATdRhiTWOWasFdSQLRVFg2zb2798/duvWLdnvBilvaMAW\r\nlAwOWotC5Zwruq7r3WCtVvO0JOo8WYNVrYDbDKpac8y8IWYmdETTOLc43GyDd1rgxjZ4qwpeK4LX\r\nnoPXS+CNLfBG2QnQiqtgqzfAyxvg1Sp4s+1Aa3KnUZ3pBAZONgG94V0b/RYHGgous08R+W5BHgTS\r\nQZ7jmfInFBXShFfPooVBvdPcLIV0nYggz261UF9fh1WvO3MD3caAtg2r0UC7RQCTw1jfhKJVwTmH\r\nzhmSiQRSqRQ45zh9+vT+gPzuwAFbUKZhh3VYXFxc0Nxvmogi3Z+X1t3r3X5a1NwAOhPgCsBgQkl0\r\nwFUDXGmD07bT2ZFRp4+u0nbqHmzbGZAwmg7AGyvg5afg1bLTwbtWdQrKbYBQp/CUS30Gue21Cb1x\r\nTWDQVfyCrIMZUkQeVeLIh4B6WEjD9vFD7GlzJVsIV4H9HTtdBXYvXYW2bTSfPYOZSiFVKICKgQ+j\r\nVIIKp7rd3u5Ay9fBmwUoxHlFmuYgmEwmU1JSICpgG7wwRyqLVNx6BgBobWz0pv1ks2je/tiZmAgC\r\nlW2BtQsgKgF4B7xtAIoBTtvgaDqHtZxKMhBR/2BbTtVY5Tn41jp4owLUtsCrW+CVMmCY3Wk7VCFg\r\npgSrv1GmGOMgQ4CLECCsEACHBXQ34A4Ktv9xd1ZHN0iWq9ekET23X9yoyrDJVOcn29+pHoDVaGC7\r\nXgd1gyPGYFLnG29tbkM9WIO5zbtrSbtVZrOzs1PSaC4JyO+SuMPDQREeFZGg8vbbbx+UC0zsVqvr\r\nebVsFs0r18BsBi1hgzcM8FYbSKhOdRdtgUIHhwbGqbOEhGaCq21nEibj4EYTvLohBiJqQL0CXtsC\r\nr5ZBms1uy13iDh7Y8BTVeAIz0XyHD9KKJcLzGhGqu5c2Yi9A7qfOfhV2MxHyuVKYjXGFY5wyPLJV\r\nEGZ7+igT0lvgprsKqXQMy3JWF7M3GlD1BkwAuZlxABwJzVlXQ9d13ffrTvsAPHA9r1znQNxMQ9c2\r\n2M6sCMaY43cZh0otcJPD3ihCGZ8GKAMnTXCigXEF1CJgCQaiGYCiOfnFdh1o1p01KZoNB96GMwyM\r\n7boXUAEmzQJ2WYKZebtGBQUuZEjLEBT/DaO8nxbIcR9zAeYSwAlwzBGOvOD0OSPYtAkIWBdeKgqA\r\nXCV2waUSwNTz68eg6DWnFRecnLCu65iYmJgUv+q0j+f1sKnGVF0PwLOzs6MeeH3Nj2sX3gelBAps\r\ncANgdgOEaqCZPJjdBGwVxKJAh4MkOk5f3o4BbrSdgQqzIzp5O3W6vFFxestKERaRag2oSmDZ4uct\r\npreNApj3ud0ZAMy9VN9BQUZMC+HPEOQJMA0gy3up+YYNPGAUID0gu6WRhHhuu/BS6XnU7CWAx/Zl\r\nxHAmoKlKL1Ds/bpTn2CSAAXmg8we7h5oamoq7zH+YtzczQtym4GBOsUwlnNW7FIZvNEETWXA2x3Q\r\nVhtcqYiF/0TukHEQSwRrbWd6D5p1Z3aEZAWIPKhDnJhPXsaN9KNwCK8r32YBA3OfpQrHVdyo5ykA\r\nxgHMAtBcjSCOGFgA7oiZ3ERKUblF6hCDFN1ATiaOc8cDMMB65HzeqbzanVigaSqSyaQLrxKQJIhd\r\nEkliKLBTwyBXMLlFzZyj+tG74IyDQJRAGs5XhCgAsw3wtgFSKYOlUqB6GkTTQZSEs84a4yC26GrT\r\nbjot6e3eooBEKlXsTjIAoOYIGJwON3K7B4Lh/8Lg7QwI4l6rMPYIVvd6QkA7JdfmSqWfnADLom7Z\r\nhVceAHBnVsiq61KoSArctoB83QQlQHo076k/paKi7bd+67cOnDlz5nmfkbWBPG8gyKI/rwdeV3kt\r\ni4mllgBq9tJWnsE+hQNmE0w1nRb9igoCKvonmCC2KQo/nH272Ry3c6Q4qUTcVlK+wQbuXUCO7BJe\r\n//U40EUFcS9SheMAnhbAjiO8jz4H8JQDW9Icuh2WQAZYDB2L6L6XogKQFZ8XpU43nV4VFOQ8M4mb\r\n45XhJRGlkZ7HOOdwSyG7/1g01TNNBoUDFMIyKFLJvWixy23xHzomODHFhGHiwEq8wRgn0mB5kGEl\r\nAFEIqC6a6gzgc3nAm+URIHcGBIx9Rl44DNyssAbZGLlhA8Bjycty4W1lOCGK0bvZBR/cXYgZoIh+\r\nMpnCrJPX5yxORSMZJs8bVuuwcx6WpLxG8QlSjINR0h3dcqayA6QjYJTSzoS46wfzHqyEOCG91PWp\r\nmx+Xb0tvSR0hMNp8B6BxWmwP8njYSHOUerJPSYXD7MOY62dj5oUBxy5Y8hffzR64WQUJ5H51tHLu\r\nKzs23yvtY6Z4RV3ljUyPDWMb+n+4UrbBbrXAmPNtM01AU9FdTdV9Wd0Fr0nQV6M3pQWy6pJgmXQh\r\n1sYI2ht8h8oOaxvC4GUhlZTDeOC9UuGw10EBFADMCG/LBigA2gRQ9T3Hr7qKLI8S0P4hWZVzKDaQ\r\nSCWQm9zfK6IGRzqZkLMNPECBEXbfUPBK/8x5U7ruyTZ0LA5dFZ3w3SUfhPoGJT1kFSXEzQm601gk\r\nryurr3tbHEobCe+F6e9STIYEN2ikea/swzDH4X2gnQYw4QvCImdkuIMKAFZ8Xwx5So0/j0V96uuH\r\nNy0uU0tjSBemRW2qCYhVjNyp9IOuCEQH+PykElGOZrPZfUJ6erprHTjj3XJdQ5qxANFK193cxQ3k\r\n29IXUgSjvDuvjPtm8sqljZwDUAi00XiqyHYBSgvR09OjHhvGSgwC7UmhtkrMAnf/+XoKZ1Vr+TH/\r\n7zkNyGvJ88TkEkR3OrCeykDP5iFPGDQ6FgKsaKxKUDUilgmFeXt721PM4dbzMsZhWRxcJY4npz2z\r\n2FXYILtAgu0BAQcHcTIWvh8SWWUJBxKjBEaZB8V0Q6V7gxQ8aPYEhvgSsAGtRBS0kwFKO8g8OPey\r\nDeBJwH5h1Vo7il8CgM4I0EbGF3o+kCsiEPT8ig+0fKs6wOcoDcJwLn9T9EJBsg0MtuiB0PYFVWHR\r\nIA+6LsFKSG/YMsgauLcTBYKgSeV7kS4Lgxe7/Nln2F1N8KhQWw3xphPxiDTgY1+QxgNqY4PAVQMU\r\nWfHZhqnffl2oLvH2EPAqb9ipiIR30PfehddNlTHGYVqip4Gon6XSuyfqzgMQMfYNycfKmQYZQRIQ\r\njHWfTwkSBaBTGS5NFiddxgcM2PYykJO3DIAFH7RRahtVLtkGUAzYP2y8VgbVD7acZcgAyB2ZRvbE\r\nfK/IgfMdrvXatWvrIW4r8C2pA6ouA8C3t7dbnU6n+4Tk6KjTtEIEcqaUf26KnGIXACvAPsjQDlmR\r\n4EKcmqZoV1jsTAMfUIX7ZRt2ax+igGUC1gWhZoNkHuJ43Y2A+8KKXAKDtgDV1QS88//8dcGqLZSX\r\nA9SpNLTF6GypVGrBW9TaF2A1yiIEndfl5eVnW1tb3R1SY2NdeGk66wRqAt5t8eL7WYQ4VqJ3Xw9J\r\nz2MibaZlCWjCad+720xDv1QZhoBmUOX13z8tRsX6KWu/eaX9HrNCvC7tU2xAfKqrBEA8AkDLJDD2\r\naweBZFLAq3ffWcuw0Gw2XdsQVJHNwpwOjam6QQvWeP6ys7NO0+hMVkx55mAcaHNpXT7fhj63g673\r\nLnng8dwpPpk5EgrVXqSmgjIJbMBtEJB1AId94Mb1dLyPf5e3rQiv2099lQAr4W55ANO/cwJqId3j\r\nklPPB8o5R61Wq0Ga8x2hvLwfvGGfow3Avn79+hNZeeWgzW1qYdi9Jc2qfYDdsbH+j3sB5oGQJ/IE\r\nUF7MsGsUuMMch4cEg0wAewTOqjV7PXQsQx1XdaNU2A9yFsDMf/UqkNR7KTKmOtepBstmaLfbqNVq\r\n1QDLwPp9L2lM1ZXl3N7c3Nw2DMPz5JH5eSfjkM44053Nnho2BlBfPgDo7sJ/fnUmlCA9RfoqG4sJ\r\nV98Ve3ahwDwCWgLgYEy1jZvLDXu8IewdH0B1w3K9sirnAcz97gnoC3kglxeezXbgFT7PtGwYhoFq\r\ntVqBtzVMJMA0RoDGfADbAKxSqbTx7Nmz7k5jR46A2TZoKu20ubd5FyzOgc2YkEYqc+BM4J02IjXp\r\neN9B1C+OgvZb+I8NufmPnQbwkogVBoG2nwL1g7gYYC+i1JYGWAi/ncgBmP/vv+ZU5OjJ3mqmtu5c\r\n54DNHOXd2tryw2tHZfdozPcpH9ACYBuG0W40Gt0n5xYXu0Xp0BIwGWAyd+VHx/s2+gAa1+8GWggG\r\nj4Vw1TczQwbqYBN3VG5QZeUDqHwBwFLMwYbd2AX5b8v3OB1yk1U4AeDE//A16It5QCVARigvTzm5\r\nU6oC4DAtB97V1dWnIao7kPIGpTWZDC4Aa2VlZVWGV0unHetg21AKo2AcaFquOjoAb3HRjHmXACPC\r\nQnTVd4w6tb4xInoWM+JnMX3vMMo7L7ZBC3B2s7XFFscuxNlceOdGdEz/d687qqupQEIX+dCMqDJM\r\noGVYaDQa4Jzj6dOnW72igcCALVZtA+/jeV2ArbW1tSdPnz717Oiqr5ofdeoALPnnncMW9sEewiJE\r\n+eEwC5Gbpy8kYOMDgBvnePNCdfcKXMTcdzNgtJLuYiNwVkd5+X/+L6AWkg68mZw09SUtFtDRYNqs\r\nC+/Pf/7zx75sgx0RsEVmGwLBBWA+ffp0q1QqeXYaf+klJ2hLpWFzoGH1oHIyDxwG53guAbxb/wvf\r\nGhF+gLUMQXqSDJUqYzFTZVG+OUp1F/YI3H656bAh1KpPdekebEe+ug8Tf/CyYxdUAqTdYE13hi0I\r\nACUBw7TRbrdRKpWKElvWbgK2sMDNA/CVK1ce1et1o16vd3eYe+01MNuGmss7RlkEbsynkAbneNYH\r\n4MGCNXgqz4Jywdlpb/AWVxHjQjusdSAif1vYo9TXMNOCqjHBjWsbpnM6Tv6733UUV3Gq/ZAUystH\r\npFENDa22iUqlgo2NjecQi8z3Ud4dsVlUtiHI83aXpd3c3Cw+efKku2Mik0Fu3z4oI7nuiqcNU1be\r\n3vUO53gi6h92axvCFZp3g7fCIh0YxriNRNgQ1oGKVJi+B/ZgWC+8LQ1MkBjZhagtDeCN//Xb0A8W\r\nBLzE8boJMcbKsw7ESgIgFO2OhXK5jPv376/4lNdCxGJIQfCGFVLtsA0AzLW1tVUZXgA48NZb4IoC\r\nNZcH40DN2gmue2kKgCuSgrJ+TfHiqDAPthCJDEF2muyJsrEBUmZh++yLOfCAFxTAufldHkNt44Cr\r\nA3j9X/4DTPzhaQda1zIkc8IyjIhUmWMZmm2z63evXLmyLImi1cfz8kE8r982eJT39u3by354F778\r\nZSfjkMuBwWlD1rC4R32ZD64KB9b7DGQMqrg7sxLO/x+Zpkhkdh+k7dY6LMTI4b6IyZj+aLy9S5/r\r\nwq0COHZ6Ggf+t9/22gWFAPqYSJHlelO7FB0tw1Hder1ee/DgwYYEbljGYaCZFP2CNhNA54MPPrhX\r\nKpUMOXDLTk0hv38/1MJYF1Q5cPND7AZy5h5CG6zITs557AAF1XZXQDOMdeAxgzO2Bx63X/5avl7b\r\nJbSuVTiS13H83/8e1PFUD1rFWSIWiVEn/0ASbpcSQEmivt1BuVzG+vr6GpxJ2R2f8rJhlRcxPG8H\r\nQOfp06eP1tfXPTse+ta3oI2OdnesWoAlcr2M7/TAXACsDGgTmC9Yi+WVKTB2gAJ0uLxvHNXtV6cw\r\niuilW6NgHhZc/+NmxCyIOB53CcCJf/97SB4ZcyYCuHZBhQMugfN1JUTsqMDmFEbHxsbGBh48ePBQ\r\nAtfdogK2yOFhBIywuZLuflM6t5w/zwEW33wTSOhQsiPd/14xg8F1r6shw7x817UPO1VbSwKTh2jk\r\nfLO4xTlxIB7pU6fwovuX8ZCiG3NI1aVwmpUcAXDgf/k60ienwGsdsEbHCZJd5dUmBOJJSXVTaGx3\r\nUCwWwTnHe++9dyNAef3qi0HyvP18r0d5z507d7NYLMKpaBNFOtPT2Pfmm1DFSJst1DcMXLn/mAsw\r\n20UaLU5Ap+rA2ALZk4qyKKAJgMWYoEZ9gTAkuP5yfrYLcPeJ9zP/b99E/q0DYHUDrG70AGbMOcF0\r\nBCBjPa9LAFDHMhSLRayurt4vl8vVmLZhIM8bNjxsSfAa5XK5Wi6Xi8vLy54dj3z729CnpsV6YIDB\r\ngKrFd4AbFsTFhhPDp9fSBRIbYPcspmP6XPn6Irztp/bCzw4Krn8gojWEXdBEwdAYgJl/9SXkvjwP\r\nVu840NY7veuNDjibBEjBmffldCoBCIXNddQbDry3b9++DacxjxECMB9mhC3I9+4I2MQ/bV+6dOni\r\nzZs3vSmzX/s15JeWAF3vfoglM1gJWYBC+gvO2RD1v8wHLQtIyUUBPMhoWRC4GVFdNWw/Bgz5vH7g\r\nximsCdoOiy/v1HeOYeTUNFitAy5Ul9UMjwKDTYCzrK+GMon6toFisYhOp4Nz587d8MFrRmQaBkqV\r\n+dVXBteF1/jggw8+LhaLKBaLnoO8/Pu/D21svHsAgwMVob62T3XVPjnaQb1upG3wqXWmQDCx3wni\r\n4vx8M8QbuOCSXXjRXpfFANe/KRHQyvePC3DH3zqAzNKYA2ndAPOrbr0Dzgtg5oSzsA3jkmVIoVI1\r\n8Pz5c6ytrd0vl8sVkbELg5fv1jaE5Xq7ylupVCqPHj26f+PGDc/Ox95+GyMHDno+1JIVDJXJ+qe4\r\nopQ6bsAWBndqBJg+FJ2FSA+gvqMRs3uHtQZxMhBB4MpgWgOqb06hGH91HumZHHi904WWC2h5zVVd\r\nAyR1EryTALeYs04e4wBR0DE1VKtNPHv2DHfv3r0lgSvDa8fJ8QLepipB8yD7rcemiU0HoCeTycT4\r\n+PiJ06dPw21/qoo2UPfO/Li74Irt7kTJjhcyEjkVksSaARx1238k97qiAtkCQWubw7aCj9GRKrGi\r\n0lIHxfuKWk0TEcfZze2wUbKqGB5WAmb9+uehJXUFh49PY2Qk2e0E6fS67f1D9zadOABSeDVAJkew\r\nUbKxvLyGR48eGX/yJ3/y5yLVXJMmc7gwmwhet2bgbENY4NZVXgDtn/70p9c2Nzdr9+7d8xzkS3/w\r\nB8gtHfao0oYFWKzfqFuYJ+a7SpnF8cmKBswsUWRGgyvRkjEHKHI+1WV4semxoG2Q4V0aYiNGcjqO\r\nfGkGSlYDLBusYXTVVlZexzoYwPhXwU3bUVxpMy0dlUobKysruHfv3sciZmz5gI3K8cZWXiC4KZP7\r\n3mT1TQBIjI6OplVVXXrjjTe6B1B1HYwQ3Pvxj7tdUWxxgBTtTWHXxAce74/0nSFL+ihurKNTIJ2n\r\nSKQItuvc07ia+CYrhqnnnAR62CzeuCNhg9xGRHDm3r8Np6dGP9VdXMpj8XABCnUWws4Qpz0/LKc7\r\nEpGXwOWAPX8S2sIp9FoTEBBwQEmj2tSwsvIUy8vL+N73vvf/VKvV50J160J5mwGqy/p9TjTm5ykL\r\nhuVTXld9W+++++7FjY0N486dO56dX//DP0Rmft6jTiXbqSxzlbUV4WOZr1aBxZhp3C9TwSIVmiOd\r\nAxaOUiQzO31vP/XVxOTD3TYgGRbkKHD7qa0CIJtRceLVCUzPZ0RDaA5COCqEwyQcxGbgDdPxvvUO\r\n2rUO6vUOtINfATdtwLIBk3UV2CIZbJSaePToEZaXl2+sra099aluVLDGh1HeIA8c5H1VAAnDMOjk\r\n5GQum80unj592qO+ufkFXP/Lv/ScaJMDIwrp3jcWw8/6X1KYog3zF7QfVYCRMQo1AbQaDvQV9LpF\r\nBqnemJQeQx9PO4j6xr0e1ybIyut+mAmVYGFfBodPFKDriqf3rqtyBhG0caBpMbQ4hwUgdeIrSC45\r\nqussKig+VzWJipVGcaOKa9eu4d133/3RkydP1oTtrkkvRYY4MliLC2/YVCXZ07v2QavX6+1Dhw69\r\nuX//fsirZM6cPIlPfvQjVEUdBBepsyRxgjd3AEAbAEC+iyBu0M45iSRFfoKAcaDcdM56WG51Xvio\r\nKHj3EmQMUIdLhMTVpQ9yZlbHSydzGJvUBbC+IhzpNuQ+eSaDkkhi9Hf/GxBVdbrJSY2mrfQonpUt\r\nfPzxDSwvLz/6q7/6q7+VwK0LeFs+y2BH+d1B4EVE5qHrf2u1Gjtw4MBMu92eev311z0Hmjh0CBe/\r\n+588J7zBgFHF+XbrYv5T3Db7ZAgUXf/K+/jQUOApQTpHUBgjqDOg3gpeEmp/n2NFZRiivOwg/rZf\r\ngzwO5xdkbFTF8ZMZzC+moKnU07KfEnk/4rntwkw5kPu134K+uORMUxP7AwRcUbGljmJjs4bLly/j\r\nJz/5yf/35MmTVQGv7HXbvlQZQ4wWp8oAlgHo3/GnC/HDhw+fHzt27I2xsTF1Zmame5DR/fux/v57\r\nKK6sej4MgwN5hXR7W8W1DTzgJUamyXbb4x8EikKQzhPMjhOYNlCTIM5L9ocPcTnofcOASwFkp1TM\r\nHU/h4KEUUknFq7QytNL9HnjFbX3fEvJv/xOxeCPpKi9AYIxOYaPBcO36dSwvLz/6/ve/H6a6Lrx2\r\nnEBtGHj7AeyxD4ZhkMnJyRxjbPGVV17p5n3BOZZ+4y2c/9//ned3weBAggAqJR7f2w9EPkAeeLCs\r\ncfRzKQjqAFSFYLJAMDdOoKpAqwOM2r1C8zjqOyy8GKKwRlOB0VkVi6+mUFjUQdO0B7jHJpAQaHv3\r\nu/uM/ZN/BSWbQ1evueN57WQaJTeNmIAAABilSURBVC2P5xtbuHbtGn7wgx/8RalUWg9Q3VbAAAXf\r\nK9sQJ3iTAVYBaOvr6+UTJ068kUgk1IMHD3bhTebzMB+tYvXqNc+Ht82ALAUKhAz8onhMJHkMNY8b\r\nzLVBYLsBqUIwNkKwb5piJk2gUmf12ThqOwy0g6ptYZpi6rCG+VMJ5Gc1KJqT+uoIGGWVJZL6eo7j\r\ng5kCyH7tN5E68aXe6pdSjnJ7YgblDseFCxdx69atG2fPnn3Pp7oNSXX9mQbspfLGUV+PfTAMg2ia\r\nxlRVPfryyy8jlUp181ALv/4Wbv2H/wPNltE9CAfQZEBBAbKEDGwdeJ9Azr/awW7zwOgWd5Adr2E6\r\nSTAySjA+TZDKEhDqrHouLeQZ2zr087b96hE0FShMEcwcVnDgSxrGF1Wk8wRUIR44jRCbQHweV/a5\r\nxFnuEfriEvK/8y+6S7gSSoRiE3RG8tjQR/Do8RPcvHnT+O53v/tdwzBKErxhXje26u5GeeETARKg\r\nvurKysrWkSNHjlar1dyrr77ahVfRdahGGyvvv+9ZH9AC0OTAAWX36sl9qTTEULqoL4t/PTan0wzx\r\nBGsZ9/1QAj1FkCsQTM5QjE4SJDOAohIoKuD2KYzrh0mfPG02B4xOEkwuUuw7SbHvZQVjCxTpPJWA\r\nJTuUtdNzqR5Qg2Cm0nMUPYXCP/3XoMlUd/V34qYhFIqtqQWUmwY++ugjXLp06ac3bty43ifD0PGN\r\nqsX+G3YdNnmGhVyo0xJZom1R85B89913/3Z0dPTf3Lx5EyeOH+9+GK//j/8Tlt/5AT65fN1dL9CJ\r\ngBlwx+I4qhJvA2ruaw4t3QhvID1YO+lBni2adPYWfIG3v1i3c71IK2kJgtEJgtHJbjyDToejYwDN\r\nbQ5LKHO9KlpoE99PHAGyIwSqRqBpQCZHoGpAJi9BKWIlwqVig5CTQ8RyC4HLG7hLh6G3dpg7ikYI\r\nkPvtfwY1P9rdkbhLLhCOxugEqibDJ598gqdPnxZ/+MMfvivVLvihNRGz8PxFKW/fhQ+3traMsbGx\r\n1NbW1r6vvPmmE7wJBR47chQPv/enMJj3IFvMGYocoWQgwMLVlMQ6KyRCpf0nrub5X06qLznAyVNU\r\ngkSSIJsjGCk428QMxeQMxdQMxdSss03OUUzNURQmCPJjFCMFgmSKIKGT0EWVIMPcVVDiUVYj4P7A\r\njIOkuulXfh2pV35dqG1vQIIQAiuVwfPcBIqbZVy8eBHvvPPOn25ubq777EJQXtefYeAvCt5+KxMG\r\nBXDKs2fPto4cOXL66dOn+muvveYu5obs4j7wWhXFSxdhcu/JKjFghABpSiJhDXu3JOTWIMFRv1PZ\r\n9CUkkwLgqC8FH3BEaGewQSIeDwCY+J5PpIYjIYGac733v/SFJWTf/q+d/0+lwQhCAEVBaXIBVZPj\r\n/fffx5UrVz746KOPzot0cty87memvH4P3F2mwDAMvrW1VZycnHwtnU5j3759opsNx+Srr2Hzp3+H\r\n+vPnYBLAAFBmwBgFEoREqmMsoPmuk7yeY7q/fe79CeGVwgqHgt4D6XMi+4M8IMABKisvDU0CBidk\r\nmNWJOYx851+Dqqqkuj0vvT06hWfQceXKVdy+fXvjz/7sz/5vCVx/kGYEBGkD/+0mVRbEUqgKl8vl\r\ndqFQSJdKpX2nTp1CNpsF4RxKQsfkq6/j0Z/+B6cg3XfiN+ydAAdF4YhZSzCoB+6nlrb4/XP/9C68\r\nvUCu338MBJUjFpSki2//58JnA2SALeJ/nASnyvQUcv/4j0HTIwB1rQK6wZqZHsHj9DiePCvi/Pnz\r\nxpkzZ/58a2trXcArB2nNPsPAfFCIlV2qbtj1QIjv37+/fvTo0SOrq6v5119/HaqigHCO5OQU9Hwe\r\nlff/DgbbefI3bKAQocCxCnPIziwEj5HT7Qf3tvT8nBQBEx6MbZSyxrcOXguBiGPsVGCHHPfBoDyv\r\nk1lIIvt7fwS1MNX1uC7oIARM0/C4MI9ys4OzZ8/iwoULP7569erlIezCp6a8UaobtHwBAUCXl5dX\r\nDh069Mbjx4/VN778ZRDmvP6JV19D89EamrduoGN7i4cBYDMA4MHyvv5LEgvafvcp4hNy78sEpm9I\r\npJ8d5jHsWoEJLPeXywMtkRQ3iex3/hjq+JwgnPYKb4TPfT42j01LwdmzZ3H37t0bP/rRj34YAm5Y\r\nkDb02NFeeN6wX8ZABe50Ovbm5uaT0dHR1yrVKk6eOuUAzDmm3vwaNn76n4HNIgxbmp4jLrdsZxg5\r\nTYf0rtx/k8QaAet3qBZ6gY8f3t7JIbsGNhpi0h/aAIBtEgC1axkSKWT+0R9DGZsVaktF4OfWLwCV\r\nwjTWeQofnT+Pu3fvbvz1X//1n3c6nVJATle2CyaiV/r5VODtF3+EqnGlUmkqitKxLOvo2Pg45ubn\r\nQRiDoicw841v4tH/+xfQTANmgAJXmFNG2S8LMZgyk9hOOOgMt8SnASlgC/t533tgpfxryP8JCwbh\r\nBmwk2OOm/+G/gTo+Jw1CEI/qNkfG8IAWcP/BQ1y9etV45513/uPW1tZjAa5fdY29tAsvCt5+tz2f\r\nwePHjzcmJycLq6urc5NTU5idnQVhDIlcDjNvfRNP3vk+NNPoWgg390YAVJlTyF5QwtVmUB/LY+Ab\r\n9M10p5QQkeNN9QUuQoF5fGD7+1wSrb7itftzwTSRRPp3/gjK+Jwvl9vLSLQyedxNTGJl7THee+89\r\n48yZM99dX1+/HwJue6/twl7CG5V9CBWc+/fvr87Ozk4/ePBgamHfPkxOTIBwhuTkJJITkyj+5MdI\r\nKYDFesOv7ph+G04xzyiFpwYVAYY77EPfmbgmfX8+wt5IE721GFKRqS8SndLqAx6NOD76KL3/Pdmy\r\n5xXgpt7+Iyjjs54BCAi7AAAdPY1byTk8L5XdfO7f3rx583JAWsw/mha7H8OnDW/UL29oNuLu3bvL\r\nBw4cOPLgwYP8cZFCo4yjcOIkMvPzePaTH0NXpOYk0ji7JXxwhgI6IbFAC4soSdgAAI9WwYabxA9Q\r\nXsRQYMTaJ67qhgMMn9WwpfuUsRmk/tG/Bc2OdgMzb2YBsBMpfJKcw5NSBWfPnsXFixffuXDhwjkJ\r\n3Ko4HXLFWBi4u/7bS3jjwrzjfK+trT2cn58/duPGjczxU6eQzWRAOUPhxCmk5xZQPPtj6AoBF512\r\nVPTK8lwfTABkafhwKeXRiuyZNRATeHeYWF6uNJ6vJbuAEvGDs4BfE/e6S5K6eBzJb/xLUD3lVIfJ\r\nXkIMQphqEjeSC3hcqrrg/s3FixfP+RTXH6D187n88wYviWkpPM8zTdNeWVm5Nzs7+9LNmzczM/Pz\r\nmJiYAGEM+ePHkd23H89/8mMkqAOtzZwWsPIoSIs7U4oyBNAIiQVe/4VCSOhz/BC10eu6mBsIRJ/d\r\n4f3twmDQRiuwBSBx+pvQv/IdEDUBQihAaG+0TQRpHS2Ja4kFrG864F66dOlvfv7zn0eBayC88OZz\r\nq7wkBsg7Xrxpmtbq6uq96enpl65fv56ZmJ7G3OwMKGPIvXQMI4cOo/ju30FhNjTqAOyvaWVChSmA\r\nTB8VjkpGh1mIoOe5GQfXPxaGUFBE2IhhlBlRACdVKN/6b6EefgWEUgGudxoPIUBHTeKqtoAnm1Wc\r\nOXMGly9f/pvLly+fA1D25XO3fQGa9aLswou2DUNlngTAd2dmZg7cvn07ny0UsLi4CMps5A4fRXbp\r\nELZ+9gHQMaApTnEa5TtrXNscqNtAUgxqxPO64RYiCio3eekGbRqPv/RTnAArLqhxAVaOTkD9598E\r\ntBMOtPBWh7mXFT2P6+o8npQqOHPmDK5cufLOlStXXMWVh36DRtDCuj3yzzu8w5QNcADcNE3r7t27\r\nNycmJqZWVlamas0mjp86BWrbGFk6hKlvfguVC38Pu9GAAuZEycxbDeTW1dZFA78UBRS58CRCTSn6\r\nV3EhAF43XZYQKbPhoCN7Cu2O/XUV6reWoH77CLg9Dd4e9+RxXeUlhGAjMYrrmMbtew9w7tw5XL16\r\n9a+vXr3qD86CRtD6BWhfCOUdFOQd28OHD+/l8/l8qVSaW1tfx7GXX4ZOCPTCKMbe+AqMJ49hVcpA\r\nx4CuAIrY01MNJDISNeYEeknihTgqJUZ9FiLMXrjwyms1DPKTD0RXi4Xuw6PTeo7ajkP7FydBD4+B\r\nUAJW2w9iZSDP+HUvH+jT+ISN4ZNbt/Czn/3MeP/99//Pe/fuXQ7wuP3AtV8kuJ8GvEF539gLra+t\r\nrT2glLY7nc7Rj2/exNKxYxhJpZEsjGLyrW+gtboK3mrC7pgg3EKBAoz1AhwqBXWmCOhsMVNZ8WUU\r\naB87QSPAMiTl5b6gLTa4PF6KK84Qpvw4zevQvnMU6tf3g6Q0MX1CBds87q1TIAQWUXBdX8ByS8P5\r\n8+dx4cKFjUuXLv35+vr6nT7BWRS4/EWA+2kpL4mhuHLmxtNgvFgsPqtUKiu6rh85f+GCni4UsLiw\r\nAFVVMPWt3wShFO1Hq1DSabTaLeQoRxK9tSD8ExZt7gxuMO6udUci/S9CPHCQbQC6K+wOHHD1zTfH\r\nHC7uXk8qUL82D+0PjoNMph1ARQE5r+8HjEJPbUFQVVK4pC3gUc3A2bNncfPmzfvnzp37062trdUA\r\nj+sPzj51cD9N2xCnhtzfa67bUrXRaNQfP378yfj4+IE7d+/mV548wcEjR5HRNBROnkLu5Ck0796B\r\nkkzC0jRYnQ7GYUMnziKGQVPCLQ60mdMzgsJNvQUPUNAIuJrodb50n5seYKDErTXoZyEigzLeS7dp\r\nb81B+/2jUI6MOnlbd9YDJQDXwDZOAFzpdri5rU7gKpnE8tpjnD17Fh9//PHfnT9//vuWZZX6KK4b\r\nnH0m4H7anrcfwJGLTVqWZTx8+PBjTdN4q9VaunTlCrSRESzOzSM1PomJb3wLxpN1WJubUMfH0U4k\r\nQDsGJmE73pd7AzoqBXYdBjTt7lxDqCHBXRjAdXiL6G0xWEExbI1uzDoF6UwRXYH61Rlo3zkI9cQY\r\niK54LIF7nZWXgPYoQAhq0PERncGq6diE8+fP1y5cuPB/LS8vXwwIzPwdbvxF5Z8quJ9FwEYilDcI\r\nZCZFrnaxWHxcrVYfJhKJ/bfu3Mk8fPYM+w8fxkgmjbE3v4bM/v1oXL8GqqogYxNoJRLQOUPB7kAl\r\nvV5l/gYaFGJBbxto2ehNSRKKHF4nQTwrScrQ6zG8arDn9T9XqqATZ4SLsV2a06C9MQntHx+AcjQP\r\nOpIAUUh3sMF5AwLc1iR46TDACG4po7ikTGP1eQlnz57F9evXf37x4sXvVSqVFQlaOaOw3Scd9qmD\r\n+1llG0iMjAMLAdgGYG9vb9dWVlau6LqeqNVq+z74+78HS6cxOz2N/PwCJr/1m0Cng9aDZdBUCmx0\r\nDJ18HgnCUbBNJMBAxKr0lAT08iJiYW8GGJajzDYn3anfCvHWNXRAdkwbsXzqO0iO2WMFurASB1gx\r\nFK4cHoH29Rloby9AOZIDzaggKu2lvKhbbeOAyzsjYM9exnOWxjltFquWo7Yffvhh7dq1a395+/bt\r\n9yzL2pSg7ZdRGLiX7mc1mPAi/2+/nmcJKW2aEjYyI7YsnJ58I5OTk4cOHTr0D3O53OL+/fvxu9/+\r\nNr564gR024SxuoJnf/Fn2L57R3yQFFRlSJSqSLY3wJ9vw2DOGnG278VJQ/u9oVK3eJsAmvAfNfHK\r\nCQGI0nseIUCaAHlp9i58l8TXd4L7017u8wTtypQO5VQB6rEcyJQOmnSAdc5eD1xQAkLFTpQAnRFU\r\n1t7ARXUGJZrC7du3ce3aNTx9+vTS7du3f9Jut0sSqHKPBRnaqNb7nyq4nyW8YdmeHU370KvxTvWB\r\nOLu0tPTVmZmZt7LZbO7gwYN4+xvfwG8cPwaNMTTv3MKz//QfYZa3PHPBNa0N3SxD2ayBP2ug03IW\r\n8La4BI8LMCGexh42gCbp8eGBjfRgzgHQfPd3m3p04ZSOTb3PVQ6lQRfTUI/nQCcToGkVRKM9QMWL\r\ndK/3AHZu1yoLuLr5GlaVURSLRXz44YfY2Nh4tLy8/MNSqfQgAFgZWjmb0AkYNXshI2dfBHgREqsE\r\ndV6XVTgpAE5LAGcAZHVdnzhw4MBbY2Njr4+MjOj79+/Hf/n1r+Nrx49DYzYa5z/E1t++A7Nc7gUx\r\n7oeuMmidOmilCbVWRafaBmm3YXPAFrbAFhM4TeJc98KNnZ1rBNhZsZa0H24HfGcnKoy4Mq+Dzieh\r\nHkxDPZQBSVKQpOIAK9sB+bUTn/ISgqadwuXSq7jXPIxisYjr169jfX29+vDhwx88e/bsEx+sLrAu\r\ntEFq229ZVf5Zqt/nAWD0AVj1qXBSUuKMf0skEuP79+//jUKh8Foul9MPHjyIb3zlK/ja8WMoqCra\r\nH19D9f2fovVgeceHLkMBZsPe3oZdq4IbbfB2G6zRADctT1slGdggBXYBVmRFndFAdAplTocyrUOZ\r\n0EBnddCUAppSpJ9/+XX1PKwr08SVfeqsgLgNHffbC7hWeg2P12u4fv06njx5Ui0Wi++vrKx8JIEq\r\nA+tXWr+3tRC8tvhnBu7nCd44NsKF2K/CfjuRliAeXVhY6EI8OTmJ106fxjdPn8ah8THY649Q/+Ac\r\nmrc+ATfavZ9d30+wex9RJHBMA8xoC64oWKPmtIMUcBHuWAAllwdNaKCaivT8cyQO1gFKoGQU8X/Q\r\nq6H1wenJz0qDDN35ZOJ1cgYUSRrrbBx3Nk/h6vUabt++jefPnxe3trbOr62tnZcgddNdssq2fXnb\r\nDoJXX+efF3A/b/D2A9jd1D5WIuUDubslEonRiYmJfzA2Nvbr6XQ6Nzo6ivn5eXzjtdfwysGDmFUo\r\n2rduonH+Q5jPn0leUgaJdpWwC7N021FF732uL5XvS0w/hz77GFS1PfDKmQFQF04fzJKcc0awSXRs\r\n0hSe8wLurs3gw/MVPHjwALVa7d7GxsZHpVLplk9ZZWBdayBDawakwD5Xavt5hjfKRvSzEnoIyJ5t\r\nbGzspXw+/0oymTycy+X0QqGAo4cO4fTBgzh18CD2KxTm7U9g3LsD89GaJwAishoqVALU7WPQe044\r\n5BREs5GYegZtogSaNLyw+n2tDKwNbFAdm1THBsniUTGLazdsXP94C6VSqba9vX19Y2PjYrPZfOqD\r\ntBWgskYAtPbnXW2/CPDGUWH/MrKJEJBdmOXLpKqqhdHR0ZfT6fRLqVTqcCaTQS6Xw/T0NI4uLuLY\r\n7CyOjI1iersB+/492KUi2GZJarhBfarbU1lIAIP07MZOwCnUwhaUXB003YQyUuvuz7ljB8qKhjLR\r\nsMlTeGaP4dFjguWHHVz/uIxyuWwYhnG3Wq1eLpfLdyQwW77Lts/PyoFYlNJ+LsH9vMPbT4X9K3F6\r\nltMKAVkG2nOfqqr5fD5/MpFIHEwkEkdSqZQ+MjICd3tpcREL4+MY1TQs2hYm2i2k6zXYW5vgzQYI\r\nIdBUr8pCAtR/uzvixZ23xEHRBkGRqtigKurZBrZUDVVNQ3HDcLZiERsbG6jVakXTNFe3t7c/qVar\r\nd6WffiMAVD+wfmsQBC37vEP7RYIXfUoAaEhQFwayDLR/c5+XSKVSi8lkcknTtAOEkOlkMplLp9NI\r\np9PIZrNQFAWFQgHz2Sy0Wg1JADOc9/obUIpJRUwJohRVSlGjFDYjoJSCUIo6pagpCkxFQUlVYVkW\r\narUaWq0WqtUqqtUqKpUKDMMoWpa1alnW02azuWwYRlEC0QjZOr5Lc0Bo8XkH94sEb5SVkJVY8amx\r\n7I1lmPtt8vM1Smk2kUjMa5p2kBAyCiBPCJlSFEXPZDLgnCOZTCKVSnXXV+6+YGEXKKUOuOJ6rVaD\r\nZVldaMVwbJUx9oxzXrYs60mz2byPXud5U1JOOStg+G77fWyQNfDbA/ZFgvaLCq8f4LCgzr9Cp6zI\r\nfp+s9bmt+fZTfV8ORVGUOUJImnNOCCEznPMUAHDOCZfV2IGYi9sVznmZEMIZY1u2bW9KwZJ/M30A\r\nB4EcBKoMrAVvgdMXGtovMrxxIA6qQ1f7wKwGgCo/FgguvE18/IVl/u5TQcVHzKeGtu9n3Q+wFQCn\r\nGQB8GLD9oP1CgftFhzcK4n4gKz4QVR/USgxg/QumB8Hr/wuqmrMDILYiYA66bvuOFQbsFx7aXyR4\r\n40AcZi1oAIhKBKhh0PZT3SD17QdwkCLbAZ41CFg+QLqL/yJ84PgFhDgqS0ECwFNC4B5kwcmwfn48\r\nAmAWopp2n8eClJUhfA3uLzywv+jwRkHcD2aC/utNEwRPmiAxVDfM+4ZNf4q6HvQlwC+qyv6ywTss\r\nyIM1lew/kbcfvGEqHHVfmBX4pQD2lxHeKJCBvWvSiBjwhtmIOPchRnqL/7J9kPglBzmOQiMC1H7H\r\n4zEgBuIvFP9LB+uv4B38fMQBfJjz2Q/AOHDyX31Yv/ob9vwM+xh/AY/96sP51d9nft5+Beiv4P1c\r\nnt9fgbnHf/8/63OKsgU7wMAAAAAASUVORK5CYII=\r\n--=_934102e9f090e995ad43d8dbc1f470c7--\r\n\r\n--=_20ef3aec043aab1e8338210905a3854b--\r\n\r\n');
/*!40000 ALTER TABLE `templates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-04-13  9:17:21
