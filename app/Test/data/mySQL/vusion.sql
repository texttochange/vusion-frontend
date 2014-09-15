-- MySQL dump 10.13  Distrib 5.5.38, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: vusion
-- ------------------------------------------------------
-- Server version	5.5.38-0ubuntu0.12.04.1

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
-- Current Database: `vusion`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `vusion` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `vusion`;

--
-- Table structure for table `acos`
--

DROP TABLE IF EXISTS `acos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(11) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `rght` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=244 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acos`
--

LOCK TABLES `acos` WRITE;
/*!40000 ALTER TABLE `acos` DISABLE KEYS */;
INSERT INTO `acos` VALUES (31,28,'',0,'add',43,44),(3,2,'',0,'index',3,4),(4,2,'',0,'view',5,6),(32,28,'',0,'edit',45,46),(5,2,'',0,'add',7,8),(6,2,'',0,'edit',9,10),(2,1,'',0,'Groups',2,13),(7,2,'',0,'delete',11,12),(33,28,'',0,'delete',47,48),(34,28,'',0,'login',49,50),(35,28,'',0,'logout',51,52),(17,16,'',0,'index',15,16),(18,16,'',0,'view',17,18),(19,16,'',0,'add',19,20),(20,16,'',0,'edit',21,22),(16,1,'',0,'Programs',14,25),(21,16,'',0,'delete',23,24),(23,22,'',0,'index',27,28),(24,22,'',0,'view',29,30),(25,22,'',0,'add',31,32),(26,22,'',0,'edit',33,34),(22,1,'',0,'ProgramsUsers',26,37),(27,22,'',0,'delete',35,36),(29,28,'',0,'index',39,40),(30,28,'',0,'view',41,42),(28,1,'',0,'Users',38,67),(36,1,'',0,'AclExtras',68,69),(37,1,'',0,'Mongodb',70,71),(44,28,'',0,'initDB',53,54),(1,0,'',0,'controllers',1,264),(72,59,NULL,NULL,'index',75,76),(59,1,NULL,NULL,'ProgramSettings',72,79),(60,59,NULL,NULL,'edit',73,74),(61,1,NULL,NULL,'ShortCodes',80,89),(62,61,NULL,NULL,'index',81,82),(63,61,NULL,NULL,'add',83,84),(64,61,NULL,NULL,'edit',85,86),(65,61,NULL,NULL,'delete',87,88),(66,28,NULL,NULL,'changePassword',55,56),(69,1,NULL,NULL,'Admin',90,93),(70,69,NULL,NULL,'index',91,92),(73,59,NULL,NULL,'view',77,78),(74,1,NULL,NULL,'ProgramHistory',94,105),(75,74,NULL,NULL,'index',95,96),(76,74,NULL,NULL,'export',97,98),(77,1,NULL,NULL,'ProgramHome',106,111),(78,77,NULL,NULL,'index',107,108),(79,1,NULL,NULL,'ProgramParticipants',112,145),(80,79,NULL,NULL,'index',113,114),(81,79,NULL,NULL,'add',115,116),(82,79,NULL,NULL,'edit',117,118),(83,79,NULL,NULL,'delete',119,120),(84,79,NULL,NULL,'view',121,122),(85,79,NULL,NULL,'import',123,124),(94,1,NULL,NULL,'UnmatchableReply',146,155),(95,94,NULL,NULL,'index',147,148),(101,1,NULL,NULL,'ProgramUnattachedMessages',156,165),(102,101,NULL,NULL,'index',157,158),(103,101,NULL,NULL,'add',159,160),(104,101,NULL,NULL,'edit',161,162),(105,101,NULL,NULL,'delete',163,164),(106,1,NULL,NULL,'Documentation',166,169),(107,106,NULL,NULL,'view',167,168),(108,1,NULL,NULL,'ProgramSimulator',170,177),(109,108,NULL,NULL,'simulate',171,172),(110,108,NULL,NULL,'send',173,174),(111,108,NULL,NULL,'receive',175,176),(112,1,NULL,NULL,'ProgramLogs',178,183),(113,112,NULL,NULL,'index',179,180),(114,112,NULL,NULL,'getBackendNotifications',181,182),(116,1,NULL,NULL,'ProgramDialogues',184,201),(117,116,NULL,NULL,'index',185,186),(118,116,NULL,NULL,'save',187,188),(119,116,NULL,NULL,'edit',189,190),(120,116,NULL,NULL,'activate',191,192),(121,116,NULL,NULL,'validateKeyword',193,194),(122,116,NULL,NULL,'testSendAllMessages',195,196),(123,1,NULL,NULL,'Templates',202,211),(124,123,NULL,NULL,'index',203,204),(125,123,NULL,NULL,'add',205,206),(126,123,NULL,NULL,'edit',207,208),(127,123,NULL,NULL,'delete',209,210),(128,1,NULL,NULL,'ProgramRequests',212,225),(129,128,NULL,NULL,'index',213,214),(130,128,NULL,NULL,'add',215,216),(131,128,NULL,NULL,'edit',217,218),(132,128,NULL,NULL,'delete',219,220),(133,128,NULL,NULL,'validateKeyword',221,222),(227,94,NULL,NULL,'paginationCount',149,150),(135,77,NULL,NULL,'restartWorker',109,110),(136,116,NULL,NULL,'delete',197,198),(137,74,NULL,NULL,'delete',99,100),(196,79,NULL,NULL,'massDelete',125,126),(197,79,NULL,NULL,'download',127,128),(198,79,NULL,NULL,'export',129,130),(199,79,NULL,NULL,'optin',131,132),(200,79,NULL,NULL,'optout',133,134),(201,79,NULL,NULL,'reset',135,136),(202,79,NULL,NULL,'massTag',137,138),(203,74,NULL,NULL,'download',101,102),(204,1,NULL,NULL,'ProgramAjax',226,229),(205,204,NULL,NULL,'getStats',227,228),(206,1,NULL,NULL,'ProgramContentVariables',230,249),(207,206,NULL,NULL,'index',231,232),(208,206,NULL,NULL,'add',233,234),(209,206,NULL,NULL,'edit',235,236),(210,206,NULL,NULL,'delete',237,238),(211,206,NULL,NULL,'indexTable',239,240),(212,206,NULL,NULL,'addTable',241,242),(213,206,NULL,NULL,'deleteTable',243,244),(214,206,NULL,NULL,'editTable',245,246),(215,206,NULL,NULL,'editTableValue',247,248),(216,116,NULL,NULL,'validateName',199,200),(217,74,NULL,NULL,'paginationCount',103,104),(218,79,NULL,NULL,'getFilterParameterOptions',139,140),(219,79,NULL,NULL,'massUntag',141,142),(220,79,NULL,NULL,'paginationCount',143,144),(221,1,NULL,NULL,'ProgramPredefinedMessages',250,259),(222,221,NULL,NULL,'index',251,252),(223,221,NULL,NULL,'add',253,254),(224,221,NULL,NULL,'edit',255,256),(225,221,NULL,NULL,'delete',257,258),(226,128,NULL,NULL,'save',223,224),(228,28,NULL,NULL,'captcha',57,58),(229,28,NULL,NULL,'requestPasswordReset',59,60),(230,28,NULL,NULL,'useTicket',61,62),(231,28,NULL,NULL,'newPassword',63,64),(234,1,NULL,NULL,'CreditViewer',260,263),(235,234,NULL,NULL,'index',261,262),(236,94,NULL,NULL,'download',151,152),(237,94,NULL,NULL,'export',153,154),(243,28,NULL,NULL,'reportIssue',65,66);
/*!40000 ALTER TABLE `acos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aros`
--

DROP TABLE IF EXISTS `aros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(11) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `rght` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aros`
--

LOCK TABLES `aros` WRITE;
/*!40000 ALTER TABLE `aros` DISABLE KEYS */;
INSERT INTO `aros` VALUES (1,0,'Group',1,'',1,10),(8,1,'User',8,'',2,3),(5,0,'Group',2,'',11,16),(18,5,'User',17,NULL,14,15),(10,6,'User',10,'',18,19),(11,7,'User',11,'',26,27),(12,7,'User',12,'',28,29),(6,0,'Group',3,'',17,24),(7,0,'Group',4,'',25,30),(13,1,'User',13,NULL,4,5),(14,1,'User',14,NULL,6,7),(15,1,'User',1,NULL,8,9),(17,5,'User',16,NULL,12,13),(19,NULL,'Group',5,NULL,31,34),(20,19,'User',18,NULL,32,33),(21,6,'User',19,NULL,20,21),(25,6,'User',23,NULL,22,23);
/*!40000 ALTER TABLE `aros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aros_acos`
--

DROP TABLE IF EXISTS `aros_acos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aros_acos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aro_id` int(11) NOT NULL,
  `aco_id` int(11) NOT NULL,
  `_create` varchar(2) NOT NULL DEFAULT '0',
  `_read` varchar(2) NOT NULL DEFAULT '0',
  `_update` varchar(2) NOT NULL DEFAULT '0',
  `_delete` varchar(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `aro_aco_key` (`aro_id`,`aco_id`)
) ENGINE=MyISAM AUTO_INCREMENT=175 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aros_acos`
--

LOCK TABLES `aros_acos` WRITE;
/*!40000 ALTER TABLE `aros_acos` DISABLE KEYS */;
INSERT INTO `aros_acos` VALUES (65,1,1,'1','1','1','1'),(66,5,1,'-1','-1','-1','-1'),(67,5,28,'1','1','1','1'),(68,5,16,'1','1','1','1'),(69,5,22,'1','1','1','1'),(70,5,77,'1','1','1','1'),(71,5,79,'1','1','1','1'),(105,6,116,'1','1','1','1'),(73,5,74,'1','1','1','1'),(74,5,59,'1','1','1','1'),(75,5,61,'1','1','1','1'),(76,6,1,'-1','-1','-1','-1'),(77,6,16,'-1','-1','-1','-1'),(78,6,17,'1','1','1','1'),(79,6,77,'1','1','1','1'),(80,6,79,'1','1','1','1'),(104,5,116,'1','1','1','1'),(82,6,74,'1','1','1','1'),(83,6,59,'1','1','1','1'),(84,6,72,'1','1','1','1'),(85,6,73,'1','1','1','1'),(86,6,61,'1','1','1','1'),(87,7,1,'-1','-1','-1','-1'),(88,7,17,'1','1','1','1'),(89,7,18,'1','1','1','1'),(90,7,77,'1','1','1','1'),(91,7,82,'-1','-1','-1','-1'),(92,7,81,'-1','-1','-1','-1'),(93,7,80,'1','1','1','1'),(94,7,84,'1','1','1','1'),(95,7,74,'1','1','1','1'),(96,5,94,'-1','-1','-1','-1'),(97,6,94,'-1','-1','-1','-1'),(98,5,108,'1','1','1','1'),(99,5,101,'1','1','1','1'),(100,6,108,'1','1','1','1'),(101,6,101,'1','1','1','1'),(102,5,112,'1','1','1','1'),(103,6,112,'1','1','1','1'),(106,5,123,'1','1','1','1'),(107,5,128,'1','1','1','1'),(108,6,128,'1','1','1','1'),(109,6,60,'-1','-1','-1','-1'),(110,7,75,'1','1','1','1'),(111,7,76,'1','1','1','1'),(112,7,137,'-1','-1','-1','-1'),(113,19,1,'-1','-1','-1','-1'),(114,19,17,'1','1','1','1'),(115,19,18,'1','1','1','1'),(116,19,77,'1','1','1','1'),(117,19,79,'1','1','1','1'),(118,19,75,'1','1','1','1'),(119,19,76,'1','1','1','1'),(120,19,137,'-1','-1','-1','-1'),(121,7,198,'1','1','1','1'),(122,7,197,'1','1','1','1'),(123,7,201,'-1','-1','-1','-1'),(124,7,199,'-1','-1','-1','-1'),(125,7,200,'-1','-1','-1','-1'),(126,19,101,'1','1','1','1'),(127,5,30,'1','1','1','1'),(128,6,30,'1','1','1','1'),(129,7,30,'1','1','1','1'),(130,19,30,'1','1','1','1'),(131,5,66,'1','1','1','1'),(132,6,66,'1','1','1','1'),(133,7,66,'1','1','1','1'),(134,19,66,'1','1','1','1'),(135,5,32,'1','1','1','1'),(136,6,32,'1','1','1','1'),(137,7,32,'1','1','1','1'),(138,19,32,'1','1','1','1'),(139,5,206,'1','1','1','1'),(140,5,221,'1','1','1','1'),(141,5,229,'1','1','1','1'),(142,5,204,'1','1','1','1'),(143,6,204,'1','1','1','1'),(144,6,206,'1','1','1','1'),(145,6,221,'1','1','1','1'),(146,6,229,'1','1','1','1'),(147,7,204,'1','1','1','1'),(148,7,218,'1','1','1','1'),(149,7,203,'1','1','1','1'),(150,7,229,'1','1','1','1'),(151,19,204,'1','1','1','1'),(152,19,203,'1','1','1','1'),(153,19,221,'1','1','1','1'),(154,19,229,'1','1','1','1'),(155,10,94,'1','1','1','1'),(165,25,94,'-1','-1','-1','-1'),(156,7,94,'-1','-1','-1','-1'),(157,19,94,'-1','-1','-1','-1'),(160,8,94,'1','1','1','1'),(163,11,94,'1','1','1','1'),(161,15,94,'-1','-1','-1','-1'),(166,5,234,'1','1','1','1'),(174,19,243,'1','1','1','1'),(173,7,243,'1','1','1','1'),(172,6,243,'1','1','1','1'),(171,5,243,'1','1','1','1');
/*!40000 ALTER TABLE `aros_acos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `specific_program_access` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'administrator',0,'2012-01-30 20:48:19','2012-01-30 20:48:19'),(2,'manager',0,'2012-01-30 20:49:52','2012-01-30 20:49:52'),(3,'program manager',1,'2012-01-30 20:50:00','2012-01-31 08:03:07'),(4,'partner',1,'2012-01-30 20:50:08','2012-01-31 08:03:18'),(5,'partner manager',1,'2013-01-16 09:30:16','2013-01-16 09:30:16');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `i18n`
--

DROP TABLE IF EXISTS `i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `i18n` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `locale` varchar(6) NOT NULL,
  `model` varchar(255) NOT NULL,
  `foreign_key` int(10) NOT NULL,
  `field` varchar(255) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `locale` (`locale`),
  KEY `model` (`model`),
  KEY `row_id` (`foreign_key`),
  KEY `field` (`field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `i18n`
--

LOCK TABLES `i18n` WRITE;
/*!40000 ALTER TABLE `i18n` DISABLE KEYS */;
/*!40000 ALTER TABLE `i18n` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `programs` (
  `id` varchar(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `url` varchar(50) DEFAULT NULL,
  `database` varchar(50) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'running',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs`
--

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
INSERT INTO `programs` VALUES ('4f59dee9-b4b0-48fa-bb14-1c713745968f','M4RH','m4rh','m4rh','2012-03-09 10:43:53','2014-07-25 15:35:46','running'),('4f26a450-f4f4-44fa-b391-0a123745968f','Mother Reminder System and many other stuff','mrs','mrs','2012-01-30 15:08:16','2014-07-25 09:32:10','running'),('4f59dee9-b4b0-48fa-bb14-ac713745968a','C4C','c4c','c4c','2012-03-09 10:43:53','2012-03-09 10:43:53','running'),('4f59dee9-b4b0-48fa-bb14-ac713745968c','Big Old Program','oldprogram','oldprogram','2012-03-09 10:43:53','2012-03-09 10:43:53','running');
/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programs_users`
--

DROP TABLE IF EXISTS `programs_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `programs_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` varchar(36) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs_users`
--

LOCK TABLES `programs_users` WRITE;
/*!40000 ALTER TABLE `programs_users` DISABLE KEYS */;
INSERT INTO `programs_users` VALUES (41,'4f59dee9-b4b0-48fa-bb14-ac713745968a',10),(6,'4f26a450-f4f4-44fa-b391-0a123745968f',12),(8,'4f59dee9-b4b0-48fa-bb14-ac713745968a',18),(9,'4f59dee9-b4b0-48fa-bb14-1c713745968f',19),(10,'4f26a450-f4f4-44fa-b391-0a123745968f',19),(40,'4f26a450-f4f4-44fa-b391-0a123745968f',10),(38,'4f59dee9-b4b0-48fa-bb14-1c713745968f',23),(33,'4f59dee9-b4b0-48fa-bb14-ac713745968a',11),(32,'4f26a450-f4f4-44fa-b391-0a123745968f',11),(39,'4f26a450-f4f4-44fa-b391-0a123745968f',23);
/*!40000 ALTER TABLE `programs_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `data` text,
  `expires` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('27dgtepcolta2p2gr0vj03mql6','Config|a:3:{s:9:\"userAgent\";s:32:\"ea1aa12c473bc80fa76dd72aecf1d281\";s:4:\"time\";i:1402909778;s:9:\"countdown\";i:10;}Message|a:1:{s:4:\"auth\";a:3:{s:7:\"message\";s:47:\"You are not authorized to access that location.\";s:7:\"element\";s:7:\"default\";s:6:\"params\";a:0:{}}}Auth|a:1:{s:8:\"redirect\";s:1:\"/\";}',1402909779),('2ov0fmedo7qup4np7f8gs9hs61','Config|a:3:{s:9:\"userAgent\";s:32:\"d0f783ad3270991709494a840c553113\";s:4:\"time\";i:1405506824;s:9:\"countdown\";i:10;}m4rh_db|s:4:\"m4rh\";Message|a:1:{s:4:\"auth\";a:3:{s:7:\"message\";s:47:\"You are not authorized to access that location.\";s:7:\"element\";s:7:\"default\";s:6:\"params\";a:0:{}}}Auth|a:1:{s:4:\"User\";a:6:{s:2:\"id\";s:1:\"8\";s:8:\"username\";s:6:\"marcus\";s:5:\"email\";s:23:\"marcus@texttochange.com\";s:8:\"group_id\";s:1:\"1\";s:7:\"created\";s:19:\"2012-01-30 20:56:54\";s:8:\"modified\";s:19:\"2014-05-19 13:58:25\";}}groupName|s:13:\"administrator\";',1405506824),('2q70ofv1be3sk3rk004hvcc9c7','Config|a:3:{s:9:\"userAgent\";s:32:\"01037b4058071aac5960fb16780e020a\";s:4:\"time\";i:1400838838;s:9:\"countdown\";i:10;}mrs_db|s:3:\"mrs\";',1400838839),('94il35d3j7t24l0sh3jbpfiit3','Config|a:3:{s:9:\"userAgent\";s:32:\"d238821cdec051ca65ff0b30447a71c4\";s:4:\"time\";i:1399830558;s:9:\"countdown\";i:10;}m4rh_db|s:4:\"m4rh\";',1399830564),('cbbspoc18i9hnqtpg964kohi47','Config|a:3:{s:9:\"userAgent\";s:32:\"af8d2274a5ef34f9b0d228c2a1bfe4f8\";s:4:\"time\";i:1406276889;s:9:\"countdown\";i:10;}m4rh_db|s:4:\"m4rh\";',1406276889),('csf0b6n8772jqjk674sj2eakt4','Config|a:3:{s:9:\"userAgent\";s:32:\"ea1aa12c473bc80fa76dd72aecf1d281\";s:4:\"time\";i:1403786306;s:9:\"countdown\";i:10;}m4rh_db|s:4:\"m4rh\";',1403786306),('e4cavgn39o4ahf9g8r551jfmq4','Config|a:3:{s:9:\"userAgent\";s:32:\"958e100199b3bd07e1b016f349b1c5ff\";s:4:\"time\";i:1401378885;s:9:\"countdown\";i:10;}mrs_db|s:3:\"mrs\";',1401378886),('g424oi5bqh0g96vq8evcoi25h2','Config|a:3:{s:9:\"userAgent\";s:32:\"ea1aa12c473bc80fa76dd72aecf1d281\";s:4:\"time\";i:1402909784;s:9:\"countdown\";i:10;}Message|a:1:{s:4:\"auth\";a:3:{s:7:\"message\";s:47:\"You are not authorized to access that location.\";s:7:\"element\";s:7:\"default\";s:6:\"params\";a:0:{}}}Auth|a:1:{s:4:\"User\";a:6:{s:2:\"id\";s:1:\"8\";s:8:\"username\";s:6:\"marcus\";s:5:\"email\";s:23:\"marcus@texttochange.com\";s:8:\"group_id\";s:1:\"1\";s:7:\"created\";s:19:\"2012-01-30 20:56:54\";s:8:\"modified\";s:19:\"2014-05-19 13:58:25\";}}groupName|s:13:\"administrator\";',1402909784),('iopquarroid3rbrphlfjb44ik4','Config|a:3:{s:9:\"userAgent\";s:32:\"958e100199b3bd07e1b016f349b1c5ff\";s:4:\"time\";i:1401801079;s:9:\"countdown\";i:10;}m4rh_db|s:4:\"m4rh\";',1401801082),('l1q9pvi3ek5u8cg6qvpdlf59e4','Config|a:3:{s:9:\"userAgent\";s:32:\"d238821cdec051ca65ff0b30447a71c4\";s:4:\"time\";i:1398976839;s:9:\"countdown\";i:10;}Message|a:1:{s:4:\"auth\";a:3:{s:7:\"message\";s:47:\"You are not authorized to access that location.\";s:7:\"element\";s:7:\"default\";s:6:\"params\";a:0:{}}}Auth|a:1:{s:4:\"User\";a:6:{s:2:\"id\";s:1:\"8\";s:8:\"username\";s:6:\"marcus\";s:5:\"email\";s:23:\"marcus@texttochange.com\";s:8:\"group_id\";s:1:\"1\";s:7:\"created\";s:19:\"2012-01-30 20:56:54\";s:8:\"modified\";s:19:\"2012-01-30 20:56:54\";}}groupName|s:13:\"administrator\";',1398976839),('n32fgb5qjcieco8nvnii61u0j0','Config|a:3:{s:9:\"userAgent\";s:32:\"ea1aa12c473bc80fa76dd72aecf1d281\";s:4:\"time\";i:1405323462;s:9:\"countdown\";i:10;}',1405323462),('t0cup7fje7bnhl4hf10nqo1ub3','Config|a:3:{s:9:\"userAgent\";s:32:\"0e6c1542a8f630a85580d7bf38be3cce\";s:4:\"time\";i:1406889436;s:9:\"countdown\";i:10;}m4rh_db|s:4:\"m4rh\";',1406889436);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(30) NOT NULL,
  `group_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  UNIQUE KEY `users_username_key` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (17,'jan','eaf35d49b7fe974eca4ef4b8a8c775f7a8b7d270','jan@texttochange.com',2,'2012-05-17 09:28:14','2012-05-17 09:28:14'),(10,'maureen','c2260807724f3796957651b60b5bd99eaba9c3ec','maureen@texttochange.com',3,'2012-01-30 20:57:40','2014-07-23 09:34:39'),(11,'unicef','edcd5da41fb73b732af57a5c810ea7735fef646f','unicef@texttochange.com',4,'2012-01-30 20:58:11','2014-05-20 12:32:42'),(12,'unilever','5fa3c44a0dbeb76daafe1bbb62d1954c4d556621','unilever@texttochange.com',4,'2012-01-30 20:58:38','2012-01-30 20:58:38'),(8,'marcus','e8d58c12a82e4471319b6fb5ec8610807d6cda98','marcus@texttochange.com',1,'2012-01-30 20:56:54','2014-05-19 13:58:25'),(18,'giz','8cb5380f6b2e8b9db3ce4555266e47dfcde028d3','giz@texttochange.com',5,'2013-01-16 09:31:41','2013-01-16 09:31:41'),(19,'testPC','d6b7a45aa446d0498dae453f1600155d0e4b5701','testPC@texttochange.com',3,'2013-02-21 14:41:38','2013-02-21 14:41:38'),(23,'newpm','91f85d707e39df6dc6c9f12b9d8f502da55aa2a9','newpm@texttochange.com',3,'2014-05-27 08:40:44','2014-07-09 18:33:47');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-08-11 19:57:42
