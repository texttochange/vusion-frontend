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
-- Dumping data for table `acos`
--

LOCK TABLES `acos` WRITE;
/*!40000 ALTER TABLE `acos` DISABLE KEYS */;
INSERT INTO `acos` VALUES (31,28,'',0,'add',43,44),(3,2,'',0,'index',3,4),(4,2,'',0,'view',5,6),(32,28,'',0,'edit',45,46),(5,2,'',0,'add',7,8),(6,2,'',0,'edit',9,10),(2,1,'',0,'Groups',2,13),(7,2,'',0,'delete',11,12),(33,28,'',0,'delete',47,48),(34,28,'',0,'login',49,50),(35,28,'',0,'logout',51,52),(17,16,'',0,'index',15,16),(18,16,'',0,'view',17,18),(19,16,'',0,'add',19,20),(20,16,'',0,'edit',21,22),(16,1,'',0,'Programs',14,25),(21,16,'',0,'delete',23,24),(23,22,'',0,'index',27,28),(24,22,'',0,'view',29,30),(25,22,'',0,'add',31,32),(26,22,'',0,'edit',33,34),(22,1,'',0,'ProgramsUsers',26,37),(27,22,'',0,'delete',35,36),(29,28,'',0,'index',39,40),(30,28,'',0,'view',41,42),(28,1,'',0,'Users',38,57),(36,1,'',0,'AclExtras',58,59),(37,1,'',0,'Mongodb',60,61),(44,28,'',0,'initDB',53,54),(1,0,'',0,'controllers',1,302),(72,59,NULL,NULL,'index',65,66),(59,1,NULL,NULL,'ProgramSettings',62,69),(60,59,NULL,NULL,'edit',63,64),(61,1,NULL,NULL,'ShortCodes',70,79),(62,61,NULL,NULL,'index',71,72),(63,61,NULL,NULL,'add',73,74),(64,61,NULL,NULL,'edit',75,76),(65,61,NULL,NULL,'delete',77,78),(66,28,NULL,NULL,'changePassword',55,56),(69,1,NULL,NULL,'Admin',80,83),(70,69,NULL,NULL,'index',81,82),(73,59,NULL,NULL,'view',67,68),(74,1,NULL,NULL,'ProgramHistory',84,91),(75,74,NULL,NULL,'index',85,86),(76,74,NULL,NULL,'export',87,88),(77,1,NULL,NULL,'ProgramHome',92,97),(78,77,NULL,NULL,'index',93,94),(79,1,NULL,NULL,'ProgramParticipants',98,113),(80,79,NULL,NULL,'index',99,100),(81,79,NULL,NULL,'add',101,102),(82,79,NULL,NULL,'edit',103,104),(83,79,NULL,NULL,'delete',105,106),(84,79,NULL,NULL,'view',107,108),(85,79,NULL,NULL,'import',109,110),(138,1,NULL,NULL,'Chosen',186,301),(94,1,NULL,NULL,'UnmatchableReply',114,117),(95,94,NULL,NULL,'index',115,116),(101,1,NULL,NULL,'ProgramUnattachedMessages',118,127),(102,101,NULL,NULL,'index',119,120),(103,101,NULL,NULL,'add',121,122),(104,101,NULL,NULL,'edit',123,124),(105,101,NULL,NULL,'delete',125,126),(106,1,NULL,NULL,'Documentation',128,131),(107,106,NULL,NULL,'view',129,130),(108,1,NULL,NULL,'ProgramSimulator',132,139),(109,108,NULL,NULL,'simulate',133,134),(110,108,NULL,NULL,'send',135,136),(111,108,NULL,NULL,'receive',137,138),(112,1,NULL,NULL,'ProgramLogs',140,145),(113,112,NULL,NULL,'index',141,142),(114,112,NULL,NULL,'getBackendNotifications',143,144),(116,1,NULL,NULL,'ProgramDialogues',146,161),(117,116,NULL,NULL,'index',147,148),(118,116,NULL,NULL,'save',149,150),(119,116,NULL,NULL,'edit',151,152),(120,116,NULL,NULL,'activate',153,154),(121,116,NULL,NULL,'validateKeyword',155,156),(122,116,NULL,NULL,'testSendAllMessages',157,158),(123,1,NULL,NULL,'Templates',162,171),(124,123,NULL,NULL,'index',163,164),(125,123,NULL,NULL,'add',165,166),(126,123,NULL,NULL,'edit',167,168),(127,123,NULL,NULL,'delete',169,170),(128,1,NULL,NULL,'ProgramRequests',172,185),(129,128,NULL,NULL,'index',173,174),(130,128,NULL,NULL,'add',175,176),(131,128,NULL,NULL,'edit',177,178),(132,128,NULL,NULL,'delete',179,180),(133,128,NULL,NULL,'validateKeyword',181,182),(134,128,NULL,NULL,'getActiveDialogue',183,184),(135,77,NULL,NULL,'restartWorker',95,96),(136,116,NULL,NULL,'delete',159,160),(137,74,NULL,NULL,'delete',89,90),(139,138,NULL,NULL,'ChosenAppModel',187,300),(140,139,NULL,NULL,'bindModel',188,189),(141,139,NULL,NULL,'unbindModel',190,191),(142,139,NULL,NULL,'setSource',192,193),(143,139,NULL,NULL,'deconstruct',194,195),(144,139,NULL,NULL,'schema',196,197),(145,139,NULL,NULL,'getColumnTypes',198,199),(146,139,NULL,NULL,'getColumnType',200,201),(147,139,NULL,NULL,'hasField',202,203),(148,139,NULL,NULL,'hasMethod',204,205),(149,139,NULL,NULL,'isVirtualField',206,207),(150,139,NULL,NULL,'getVirtualField',208,209),(151,139,NULL,NULL,'create',210,211),(152,139,NULL,NULL,'read',212,213),(153,139,NULL,NULL,'field',214,215),(154,139,NULL,NULL,'saveField',216,217),(155,139,NULL,NULL,'save',218,219),(156,139,NULL,NULL,'updateCounterCache',220,221),(157,139,NULL,NULL,'saveAll',222,223),(158,139,NULL,NULL,'saveMany',224,225),(159,139,NULL,NULL,'validateMany',226,227),(160,139,NULL,NULL,'saveAssociated',228,229),(161,139,NULL,NULL,'validateAssociated',230,231),(162,139,NULL,NULL,'updateAll',232,233),(163,139,NULL,NULL,'delete',234,235),(164,139,NULL,NULL,'deleteAll',236,237),(165,139,NULL,NULL,'exists',238,239),(166,139,NULL,NULL,'hasAny',240,241),(167,139,NULL,NULL,'find',242,243),(168,139,NULL,NULL,'buildQuery',244,245),(169,139,NULL,NULL,'resetAssociations',246,247),(170,139,NULL,NULL,'isUnique',248,249),(171,139,NULL,NULL,'query',250,251),(172,139,NULL,NULL,'validates',252,253),(173,139,NULL,NULL,'invalidFields',254,255),(174,139,NULL,NULL,'invalidate',256,257),(175,139,NULL,NULL,'isForeignKey',258,259),(176,139,NULL,NULL,'escapeField',260,261),(177,139,NULL,NULL,'getID',262,263),(178,139,NULL,NULL,'getLastInsertID',264,265),(179,139,NULL,NULL,'getInsertID',266,267),(180,139,NULL,NULL,'setInsertID',268,269),(181,139,NULL,NULL,'getNumRows',270,271),(182,139,NULL,NULL,'getAffectedRows',272,273),(183,139,NULL,NULL,'setDataSource',274,275),(184,139,NULL,NULL,'getDataSource',276,277),(185,139,NULL,NULL,'associations',278,279),(186,139,NULL,NULL,'getAssociated',280,281),(187,139,NULL,NULL,'joinModel',282,283),(188,139,NULL,NULL,'beforeFind',284,285),(189,139,NULL,NULL,'afterFind',286,287),(190,139,NULL,NULL,'beforeSave',288,289),(191,139,NULL,NULL,'afterSave',290,291),(192,139,NULL,NULL,'beforeDelete',292,293),(193,139,NULL,NULL,'afterDelete',294,295),(194,139,NULL,NULL,'beforeValidate',296,297),(195,139,NULL,NULL,'onError',298,299),(196,79,NULL,NULL,'massDelete',111,112);
/*!40000 ALTER TABLE `acos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `aros_acos`
--

LOCK TABLES `aros_acos` WRITE;
/*!40000 ALTER TABLE `aros_acos` DISABLE KEYS */;
INSERT INTO `aros_acos` VALUES (65,1,1,'1','1','1','1'),(66,5,1,'-1','-1','-1','-1'),(67,5,28,'1','1','1','1'),(68,5,16,'1','1','1','1'),(69,5,22,'1','1','1','1'),(70,5,77,'1','1','1','1'),(71,5,79,'1','1','1','1'),(105,6,116,'1','1','1','1'),(73,5,74,'1','1','1','1'),(74,5,59,'1','1','1','1'),(75,5,61,'1','1','1','1'),(76,6,1,'-1','-1','-1','-1'),(77,6,16,'-1','-1','-1','-1'),(78,6,17,'1','1','1','1'),(79,6,77,'1','1','1','1'),(80,6,79,'1','1','1','1'),(104,5,116,'1','1','1','1'),(82,6,74,'1','1','1','1'),(83,6,59,'1','1','1','1'),(84,6,72,'1','1','1','1'),(85,6,73,'1','1','1','1'),(86,6,61,'1','1','1','1'),(87,7,1,'-1','-1','-1','-1'),(88,7,17,'1','1','1','1'),(89,7,18,'1','1','1','1'),(90,7,77,'1','1','1','1'),(91,7,82,'-1','-1','-1','-1'),(92,7,81,'-1','-1','-1','-1'),(93,7,80,'1','1','1','1'),(94,7,84,'1','1','1','1'),(95,7,74,'1','1','1','1'),(96,5,94,'1','1','1','1'),(97,6,94,'-1','-1','-1','-1'),(98,5,108,'1','1','1','1'),(99,5,101,'1','1','1','1'),(100,6,108,'1','1','1','1'),(101,6,101,'1','1','1','1'),(102,5,112,'1','1','1','1'),(103,6,112,'1','1','1','1'),(106,5,123,'1','1','1','1'),(107,5,128,'1','1','1','1'),(108,6,128,'1','1','1','1'),(109,6,60,'1','1','1','1'),(110,7,75,'1','1','1','1'),(111,7,76,'1','1','1','1'),(112,7,137,'-1','-1','-1','-1');
/*!40000 ALTER TABLE `aros_acos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'administrator',0,'2012-01-30 20:48:19','2012-01-30 20:48:19'),(2,'manager',0,'2012-01-30 20:49:52','2012-01-30 20:49:52'),(3,'program manager',1,'2012-01-30 20:50:00','2012-01-31 08:03:07'),(4,'customer',1,'2012-01-30 20:50:08','2012-01-31 08:03:18');
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

-- Dump completed on 2012-12-04 14:17:47
