-- MySQL dump 10.13  Distrib 5.1.66, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: vusion
-- ------------------------------------------------------
-- Server version	5.1.66-0ubuntu0.11.10.3

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
-- Dumping data for table `acos`
--

LOCK TABLES `acos` WRITE;
/*!40000 ALTER TABLE `acos` DISABLE KEYS */;
INSERT INTO `acos` VALUES (31,28,'',0,'add',43,44),(3,2,'',0,'index',3,4),(4,2,'',0,'view',5,6),(32,28,'',0,'edit',45,46),(5,2,'',0,'add',7,8),(6,2,'',0,'edit',9,10),(2,1,'',0,'Groups',2,13),(7,2,'',0,'delete',11,12),(33,28,'',0,'delete',47,48),(34,28,'',0,'login',49,50),(35,28,'',0,'logout',51,52),(17,16,'',0,'index',15,16),(18,16,'',0,'view',17,18),(19,16,'',0,'add',19,20),(20,16,'',0,'edit',21,22),(16,1,'',0,'Programs',14,25),(21,16,'',0,'delete',23,24),(23,22,'',0,'index',27,28),(24,22,'',0,'view',29,30),(25,22,'',0,'add',31,32),(26,22,'',0,'edit',33,34),(22,1,'',0,'ProgramsUsers',26,37),(27,22,'',0,'delete',35,36),(29,28,'',0,'index',39,40),(30,28,'',0,'view',41,42),(28,1,'',0,'Users',38,57),(36,1,'',0,'AclExtras',58,59),(37,1,'',0,'Mongodb',60,61),(44,28,'',0,'initDB',53,54),(1,0,'',0,'controllers',1,198),(72,59,NULL,NULL,'index',65,66),(59,1,NULL,NULL,'ProgramSettings',62,69),(60,59,NULL,NULL,'edit',63,64),(61,1,NULL,NULL,'ShortCodes',70,79),(62,61,NULL,NULL,'index',71,72),(63,61,NULL,NULL,'add',73,74),(64,61,NULL,NULL,'edit',75,76),(65,61,NULL,NULL,'delete',77,78),(66,28,NULL,NULL,'changePassword',55,56),(69,1,NULL,NULL,'Admin',80,83),(70,69,NULL,NULL,'index',81,82),(73,59,NULL,NULL,'view',67,68),(74,1,NULL,NULL,'ProgramHistory',84,91),(75,74,NULL,NULL,'index',85,86),(76,74,NULL,NULL,'export',87,88),(77,1,NULL,NULL,'ProgramHome',92,97),(78,77,NULL,NULL,'index',93,94),(79,1,NULL,NULL,'ProgramParticipants',98,125),(80,79,NULL,NULL,'index',99,100),(81,79,NULL,NULL,'add',101,102),(82,79,NULL,NULL,'edit',103,104),(83,79,NULL,NULL,'delete',105,106),(84,79,NULL,NULL,'view',107,108),(85,79,NULL,NULL,'import',109,110),(94,1,NULL,NULL,'UnmatchableReply',126,129),(95,94,NULL,NULL,'index',127,128),(101,1,NULL,NULL,'ProgramUnattachedMessages',130,139),(102,101,NULL,NULL,'index',131,132),(103,101,NULL,NULL,'add',133,134),(104,101,NULL,NULL,'edit',135,136),(105,101,NULL,NULL,'delete',137,138),(106,1,NULL,NULL,'Documentation',140,143),(107,106,NULL,NULL,'view',141,142),(108,1,NULL,NULL,'ProgramSimulator',144,151),(109,108,NULL,NULL,'simulate',145,146),(110,108,NULL,NULL,'send',147,148),(111,108,NULL,NULL,'receive',149,150),(112,1,NULL,NULL,'ProgramLogs',152,157),(113,112,NULL,NULL,'index',153,154),(114,112,NULL,NULL,'getBackendNotifications',155,156),(116,1,NULL,NULL,'ProgramDialogues',158,173),(117,116,NULL,NULL,'index',159,160),(118,116,NULL,NULL,'save',161,162),(119,116,NULL,NULL,'edit',163,164),(120,116,NULL,NULL,'activate',165,166),(121,116,NULL,NULL,'validateKeyword',167,168),(122,116,NULL,NULL,'testSendAllMessages',169,170),(123,1,NULL,NULL,'Templates',174,183),(124,123,NULL,NULL,'index',175,176),(125,123,NULL,NULL,'add',177,178),(126,123,NULL,NULL,'edit',179,180),(127,123,NULL,NULL,'delete',181,182),(128,1,NULL,NULL,'ProgramRequests',184,197),(129,128,NULL,NULL,'index',185,186),(130,128,NULL,NULL,'add',187,188),(131,128,NULL,NULL,'edit',189,190),(132,128,NULL,NULL,'delete',191,192),(133,128,NULL,NULL,'validateKeyword',193,194),(134,128,NULL,NULL,'getActiveDialogue',195,196),(135,77,NULL,NULL,'restartWorker',95,96),(136,116,NULL,NULL,'delete',171,172),(137,74,NULL,NULL,'delete',89,90),(196,79,NULL,NULL,'massDelete',111,112),(197,79,NULL,NULL,'download',113,114),(198,79,NULL,NULL,'export',115,116),(199,79,NULL,NULL,'optin',117,118),(200,79,NULL,NULL,'optout',119,120),(201,79,NULL,NULL,'reset',121,122),(202,79,NULL,NULL,'massTag',123,124);
/*!40000 ALTER TABLE `acos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aros_acos`
--

LOCK TABLES `aros_acos` WRITE;
/*!40000 ALTER TABLE `aros_acos` DISABLE KEYS */;
INSERT INTO `aros_acos` VALUES (65,1,1,'1','1','1','1'),(66,5,1,'-1','-1','-1','-1'),(67,5,28,'1','1','1','1'),(68,5,16,'1','1','1','1'),(69,5,22,'1','1','1','1'),(70,5,77,'1','1','1','1'),(71,5,79,'1','1','1','1'),(105,6,116,'1','1','1','1'),(73,5,74,'1','1','1','1'),(74,5,59,'1','1','1','1'),(75,5,61,'1','1','1','1'),(76,6,1,'-1','-1','-1','-1'),(77,6,16,'-1','-1','-1','-1'),(78,6,17,'1','1','1','1'),(79,6,77,'1','1','1','1'),(80,6,79,'1','1','1','1'),(104,5,116,'1','1','1','1'),(82,6,74,'1','1','1','1'),(83,6,59,'1','1','1','1'),(84,6,72,'1','1','1','1'),(85,6,73,'1','1','1','1'),(86,6,61,'1','1','1','1'),(87,7,1,'-1','-1','-1','-1'),(88,7,17,'1','1','1','1'),(89,7,18,'1','1','1','1'),(90,7,77,'1','1','1','1'),(91,7,82,'-1','-1','-1','-1'),(92,7,81,'-1','-1','-1','-1'),(93,7,80,'1','1','1','1'),(94,7,84,'1','1','1','1'),(95,7,74,'1','1','1','1'),(96,5,94,'1','1','1','1'),(97,6,94,'-1','-1','-1','-1'),(98,5,108,'1','1','1','1'),(99,5,101,'1','1','1','1'),(100,6,108,'1','1','1','1'),(101,6,101,'1','1','1','1'),(102,5,112,'1','1','1','1'),(103,6,112,'1','1','1','1'),(106,5,123,'1','1','1','1'),(107,5,128,'1','1','1','1'),(108,6,128,'1','1','1','1'),(109,6,60,'1','1','1','1'),(110,7,75,'1','1','1','1'),(111,7,76,'1','1','1','1'),(112,7,137,'-1','-1','-1','-1'),(113,19,1,'-1','-1','-1','-1'),(114,19,17,'1','1','1','1'),(115,19,18,'1','1','1','1'),(116,19,77,'1','1','1','1'),(117,19,79,'1','1','1','1'),(118,19,75,'1','1','1','1'),(119,19,76,'1','1','1','1'),(120,19,137,'-1','-1','-1','-1'),(121,7,198,'1','1','1','1'),(122,7,197,'1','1','1','1'),(123,7,201,'-1','-1','-1','-1'),(124,7,199,'-1','-1','-1','-1'),(125,7,200,'-1','-1','-1','-1'),(126,19,101,'1','1','1','1'),(127,5,30,'1','1','1','1'),(128,6,30,'1','1','1','1'),(129,7,30,'1','1','1','1'),(130,19,30,'1','1','1','1'),(131,5,66,'1','1','1','1'),(132,6,66,'1','1','1','1'),(133,7,66,'1','1','1','1'),(134,19,66,'1','1','1','1');
/*!40000 ALTER TABLE `aros_acos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'administrator',0,'2012-01-30 20:48:19','2012-01-30 20:48:19'),(2,'manager',0,'2012-01-30 20:49:52','2012-01-30 20:49:52'),(3,'program manager',1,'2012-01-30 20:50:00','2012-01-31 08:03:07'),(4,'partner',1,'2012-01-30 20:50:08','2012-01-31 08:03:18'),(5,'program messager',1,'2013-01-16 09:30:16','2013-01-16 09:30:16');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-02-21  0:46:33
