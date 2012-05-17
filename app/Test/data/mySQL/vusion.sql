-- MySQL dump 10.13  Distrib 5.5.16, for Linux (i686)
--
-- Host: localhost    Database: vusion
-- ------------------------------------------------------
-- Server version	5.5.16

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
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acos`
--

LOCK TABLES `acos` WRITE;
/*!40000 ALTER TABLE `acos` DISABLE KEYS */;
INSERT INTO `acos` VALUES (31,28,'',0,'add',43,44),(3,2,'',0,'index',3,4),(4,2,'',0,'view',5,6),(32,28,'',0,'edit',45,46),(5,2,'',0,'add',7,8),(6,2,'',0,'edit',9,10),(2,1,'',0,'Groups',2,13),(7,2,'',0,'delete',11,12),(33,28,'',0,'delete',47,48),(34,28,'',0,'login',49,50),(35,28,'',0,'logout',51,52),(17,16,'',0,'index',15,16),(18,16,'',0,'view',17,18),(19,16,'',0,'add',19,20),(20,16,'',0,'edit',21,22),(16,1,'',0,'Programs',14,25),(21,16,'',0,'delete',23,24),(23,22,'',0,'index',27,28),(24,22,'',0,'view',29,30),(25,22,'',0,'add',31,32),(26,22,'',0,'edit',33,34),(22,1,'',0,'ProgramsUsers',26,37),(27,22,'',0,'delete',35,36),(29,28,'',0,'index',39,40),(30,28,'',0,'view',41,42),(28,1,'',0,'Users',38,57),(36,1,'',0,'AclExtras',58,59),(37,1,'',0,'Mongodb',60,61),(44,28,'',0,'initDB',53,54),(1,0,'',0,'controllers',1,180),(72,59,NULL,NULL,'index',65,66),(59,1,NULL,NULL,'ProgramSettings',62,69),(60,59,NULL,NULL,'edit',63,64),(61,1,NULL,NULL,'ShortCodes',70,79),(62,61,NULL,NULL,'index',71,72),(63,61,NULL,NULL,'add',73,74),(64,61,NULL,NULL,'edit',75,76),(65,61,NULL,NULL,'delete',77,78),(66,28,NULL,NULL,'changePassword',55,56),(69,1,NULL,NULL,'Admin',80,83),(70,69,NULL,NULL,'index',81,82),(73,59,NULL,NULL,'view',67,68),(74,1,NULL,NULL,'ProgramHistory',84,89),(75,74,NULL,NULL,'index',85,86),(76,74,NULL,NULL,'export',87,88),(77,1,NULL,NULL,'ProgramHome',90,93),(78,77,NULL,NULL,'index',91,92),(79,1,NULL,NULL,'ProgramParticipants',94,109),(80,79,NULL,NULL,'index',95,96),(81,79,NULL,NULL,'add',97,98),(82,79,NULL,NULL,'edit',99,100),(83,79,NULL,NULL,'delete',101,102),(84,79,NULL,NULL,'view',103,104),(85,79,NULL,NULL,'import',105,106),(86,79,NULL,NULL,'checkPhoneNumber',107,108),(94,1,NULL,NULL,'UnmatchableReply',110,113),(95,94,NULL,NULL,'index',111,112),(101,1,NULL,NULL,'ProgramUnattachedMessages',114,123),(102,101,NULL,NULL,'index',115,116),(103,101,NULL,NULL,'add',117,118),(104,101,NULL,NULL,'edit',119,120),(105,101,NULL,NULL,'delete',121,122),(106,1,NULL,NULL,'Documentation',124,127),(107,106,NULL,NULL,'view',125,126),(108,1,NULL,NULL,'ProgramSimulator',128,135),(109,108,NULL,NULL,'simulate',129,130),(110,108,NULL,NULL,'send',131,132),(111,108,NULL,NULL,'receive',133,134),(112,1,NULL,NULL,'ProgramLogs',136,141),(113,112,NULL,NULL,'index',137,138),(114,112,NULL,NULL,'getBackendNotifications',139,140),(116,1,NULL,NULL,'ProgramDialogues',142,155),(117,116,NULL,NULL,'index',143,144),(118,116,NULL,NULL,'save',145,146),(119,116,NULL,NULL,'edit',147,148),(120,116,NULL,NULL,'activate',149,150),(121,116,NULL,NULL,'validateKeyword',151,152),(122,116,NULL,NULL,'testSendAllMessages',153,154),(123,1,NULL,NULL,'Templates',156,165),(124,123,NULL,NULL,'index',157,158),(125,123,NULL,NULL,'add',159,160),(126,123,NULL,NULL,'edit',161,162),(127,123,NULL,NULL,'delete',163,164),(128,1,NULL,NULL,'ProgramRequests',166,179),(129,128,NULL,NULL,'index',167,168),(130,128,NULL,NULL,'add',169,170),(131,128,NULL,NULL,'edit',171,172),(132,128,NULL,NULL,'delete',173,174),(133,128,NULL,NULL,'validateKeyword',175,176),(134,128,NULL,NULL,'getActiveDialogue',177,178);
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
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aros`
--

LOCK TABLES `aros` WRITE;
/*!40000 ALTER TABLE `aros` DISABLE KEYS */;
INSERT INTO `aros` VALUES (1,0,'Group',1,'',1,10),(8,1,'User',8,'',2,3),(5,0,'Group',2,'',11,16),(9,5,'User',9,'',12,13),(10,6,'User',10,'',18,19),(11,7,'User',11,'',22,23),(12,7,'User',12,'',24,25),(6,0,'Group',3,'',17,20),(7,0,'Group',4,'',21,26),(13,1,'User',13,NULL,4,5),(14,1,'User',14,NULL,6,7),(15,1,'User',1,NULL,8,9),(17,5,'User',16,NULL,14,15);
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
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aros_acos`
--

LOCK TABLES `aros_acos` WRITE;
/*!40000 ALTER TABLE `aros_acos` DISABLE KEYS */;
INSERT INTO `aros_acos` VALUES (65,1,1,'1','1','1','1'),(66,5,1,'-1','-1','-1','-1'),(67,5,28,'1','1','1','1'),(68,5,16,'1','1','1','1'),(69,5,22,'1','1','1','1'),(70,5,77,'1','1','1','1'),(71,5,79,'1','1','1','1'),(105,6,116,'1','1','1','1'),(73,5,74,'1','1','1','1'),(74,5,59,'1','1','1','1'),(75,5,61,'1','1','1','1'),(76,6,1,'-1','-1','-1','-1'),(77,6,16,'-1','-1','-1','-1'),(78,6,17,'1','1','1','1'),(79,6,77,'1','1','1','1'),(80,6,79,'1','1','1','1'),(104,5,116,'1','1','1','1'),(82,6,74,'1','1','1','1'),(83,6,59,'-1','-1','-1','-1'),(84,6,72,'1','1','1','1'),(85,6,73,'1','1','1','1'),(86,6,61,'1','1','1','1'),(87,7,1,'-1','-1','-1','-1'),(88,7,17,'1','1','1','1'),(89,7,18,'1','1','1','1'),(90,7,77,'1','1','1','1'),(91,7,82,'-1','-1','-1','-1'),(92,7,81,'-1','-1','-1','-1'),(93,7,80,'1','1','1','1'),(94,7,84,'1','1','1','1'),(95,7,74,'1','1','1','1'),(96,5,94,'1','1','1','1'),(97,6,94,'1','1','1','1'),(98,5,108,'1','1','1','1'),(99,5,101,'1','1','1','1'),(100,6,108,'1','1','1','1'),(101,6,101,'1','1','1','1'),(102,5,112,'1','1','1','1'),(103,6,112,'1','1','1','1'),(106,5,123,'1','1','1','1'),(107,5,128,'1','1','1','1'),(108,6,128,'1','1','1','1');
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'administrator',0,'2012-01-30 20:48:19','2012-01-30 20:48:19'),(2,'manager',0,'2012-01-30 20:49:52','2012-01-30 20:49:52'),(3,'program manager',1,'2012-01-30 20:50:00','2012-01-31 08:03:07'),(4,'customer',1,'2012-01-30 20:50:08','2012-01-31 08:03:18');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
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
  `country` varchar(50) DEFAULT NULL,
  `url` varchar(50) DEFAULT NULL,
  `database` varchar(50) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs`
--

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
INSERT INTO `programs` VALUES ('4f59dee9-b4b0-48fa-bb14-1c713745968f','M4H','','m4h','m4h','2012-03-09 10:43:53','2012-03-09 10:43:53'),('4f26a450-f4f4-44fa-b391-0a123745968f','Mother Reminder System','congo','mrs','mrs','2012-01-30 15:08:16','2012-01-30 15:08:16'),('4f337849-65d8-4849-9038-11963745968f','wikipedia','kenya','wiki','wiki','2012-02-09 07:39:53','2012-02-09 07:39:53'),('4f62f303-576c-4d08-b70f-0c6c3745968f','AMREF',NULL,'amref','amref','2012-03-16 08:00:03','2012-03-16 08:00:03');
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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs_users`
--

LOCK TABLES `programs_users` WRITE;
/*!40000 ALTER TABLE `programs_users` DISABLE KEYS */;
INSERT INTO `programs_users` VALUES (7,'4f337849-65d8-4849-9038-11963745968f',10),(6,'4f26a450-f4f4-44fa-b391-0a123745968f',12);
/*!40000 ALTER TABLE `programs_users` ENABLE KEYS */;
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
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (9,'jan','a47dc5b657cbdd4a961835a6f7e9caa5ee9ab1ac','jan@texttochange.com',2,'2012-01-30 20:57:17','2012-01-30 20:57:17'),(10,'maureen','c2260807724f3796957651b60b5bd99eaba9c3ec','maureen@texttochange.com',3,'2012-01-30 20:57:40','2012-03-15 11:22:13'),(11,'unicef','edcd5da41fb73b732af57a5c810ea7735fef646f','unicef@texttochange.com',4,'2012-01-30 20:58:11','2012-01-30 20:58:11'),(12,'unilever','5fa3c44a0dbeb76daafe1bbb62d1954c4d556621','unilever@texttochange.com',4,'2012-01-30 20:58:38','2012-01-30 20:58:38'),(8,'marcus','e8d58c12a82e4471319b6fb5ec8610807d6cda98','marcus@texttochange.com',1,'2012-01-30 20:56:54','2012-01-30 20:56:54');
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

-- Dump completed on 2012-05-17 12:26:22
