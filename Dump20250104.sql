CREATE DATABASE  IF NOT EXISTS `u415861906_infosec2222` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `u415861906_infosec2222`;
-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: localhost    Database: u415861906_infosec2222
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `attendanceID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int NOT NULL,
  `TimeIn` time NOT NULL,
  `TimeOut` time NOT NULL,
  `date` date NOT NULL,
  `hoursWorked` int NOT NULL,
  PRIMARY KEY (`attendanceID`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,1,'08:00:00','16:00:00','2025-01-01',8),(2,2,'09:00:00','17:00:00','2025-01-01',8),(3,3,'08:30:00','16:30:00','2025-01-01',8),(4,4,'07:45:00','15:45:00','2025-01-01',8),(5,5,'08:15:00','16:15:00','2025-01-01',8),(6,6,'08:00:00','16:00:00','2025-01-02',8),(7,7,'09:00:00','17:00:00','2025-01-02',8),(8,8,'08:30:00','16:30:00','2025-01-02',8),(9,9,'07:45:00','15:45:00','2025-01-02',8),(10,10,'08:15:00','16:15:00','2025-01-02',8),(11,11,'08:00:00','16:00:00','2025-01-03',8),(12,12,'09:00:00','17:00:00','2025-01-03',8),(13,13,'08:30:00','16:30:00','2025-01-03',8),(14,14,'07:45:00','15:45:00','2025-01-03',8),(15,15,'08:15:00','16:15:00','2025-01-03',8),(16,16,'08:00:00','16:00:00','2025-01-04',8),(17,17,'09:00:00','17:00:00','2025-01-04',8),(18,18,'08:30:00','16:30:00','2025-01-04',8),(19,19,'07:45:00','15:45:00','2025-01-04',8),(20,20,'08:15:00','16:15:00','2025-01-04',8),(21,21,'08:00:00','16:00:00','2025-01-05',8),(22,22,'09:00:00','17:00:00','2025-01-05',8),(23,23,'08:30:00','16:30:00','2025-01-05',8),(24,24,'07:45:00','15:45:00','2025-01-05',8),(25,25,'08:15:00','16:15:00','2025-01-05',8),(26,26,'08:00:00','16:00:00','2025-01-06',8),(27,27,'09:00:00','17:00:00','2025-01-06',8),(28,28,'08:30:00','16:30:00','2025-01-06',8),(29,29,'07:45:00','15:45:00','2025-01-06',8),(30,30,'08:15:00','16:15:00','2025-01-06',8),(31,31,'08:00:00','16:00:00','2025-01-07',8),(32,32,'09:00:00','17:00:00','2025-01-07',8),(33,33,'08:30:00','16:30:00','2025-01-07',8),(34,34,'07:45:00','15:45:00','2025-01-07',8),(35,35,'08:15:00','16:15:00','2025-01-07',8),(36,36,'08:00:00','16:00:00','2025-01-08',8),(37,37,'09:00:00','17:00:00','2025-01-08',8),(38,38,'08:30:00','16:30:00','2025-01-08',8),(39,39,'07:45:00','15:45:00','2025-01-08',8),(40,40,'08:15:00','16:15:00','2025-01-08',8),(41,41,'08:00:00','16:00:00','2025-01-09',8),(42,42,'09:00:00','17:00:00','2025-01-09',8),(43,43,'08:30:00','16:30:00','2025-01-09',8),(44,44,'07:45:00','15:45:00','2025-01-09',8),(45,45,'08:15:00','16:15:00','2025-01-09',8),(46,46,'08:00:00','16:00:00','2025-01-10',8),(47,47,'09:00:00','17:00:00','2025-01-10',8),(48,48,'08:30:00','16:30:00','2025-01-10',8),(49,49,'07:45:00','15:45:00','2025-01-10',8),(50,50,'08:15:00','16:15:00','2025-01-10',8);
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee` (
  `employeeID` int NOT NULL AUTO_INCREMENT,
  `firstName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contactInformation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `leaveBalance` int NOT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`employeeID`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee`
--

LOCK TABLES `employee` WRITE;
/*!40000 ALTER TABLE `employee` DISABLE KEYS */;
INSERT INTO `employee` VALUES (1,'John','Doe','IT Department','john.doe@example.com',15,'Developer'),(2,'Jane','Smith','HR Department','jane.smith@example.com',10,'Manager'),(3,'Blix','Foryasen','Finance Department','blix.foryasen@example.com',20,'Analyst'),(4,'Jerico','Lim','HR Department','jerico.lim@example.com',12,'Coordinator'),(5,'Jessy','Mapanao','Finance Department','jessy.mapanao@example.com',18,'Supervisor'),(6,'Gian','Colinares','IT Department','gian.colinares@example.com',16,'Engineer'),(7,'Franze','Natividad','HR Department','franze.natividad@example.com',14,'Assistant'),(8,'Hanni','Pham','Finance Department','hanni.pham@example.com',13,'Consultant'),(9,'Henry','Walker','HR Department','henry.walker@example.com',9,'Specialist'),(10,'Ivy','Hall','Finance Department','ivy.hall@example.com',20,'Supervisor'),(11,'Pajeet','Patel','IT Department','pajeet.patel@example.com',17,'Tester'),(12,'Karen','Young','HR Department','karen.young@example.com',15,'Recruiter'),(13,'Leo','King','Finance Department','leo.king@example.com',11,'Auditor'),(14,'Mia','Scott','IT Department','mia.scott@example.com',10,'Designer'),(15,'Nathan','Adams','Finance Department','nathan.adams@example.com',14,'Operator'),(16,'Rasheed','Agarwal','IT Department','rasheed.agarwal@example.com',19,'Programmer'),(17,'Paul','Mitchell','HR Department','paul.mitchell@example.com',12,'Trainer'),(18,'Quinn','Perez','Finance Department','quinn.perez@example.com',16,'Accountant'),(19,'Rachel','Sanchez','HR Department','rachel.sanchez@example.com',13,'Strategist'),(20,'Samuel','Morgan','Finance Department','samuel.morgan@example.com',18,'Coordinator'),(21,'Aram','Mojtabai','IT Department','aram.mojtabai@example.com',15,'Consultant'),(22,'Ulysses','Bell','HR Department','ulysses.bell@example.com',14,'Manager'),(23,'Vera','Ramirez','Finance Department','vera.ramirez@example.com',12,'Analyst'),(24,'Walter','Foster','HR Department','walter.foster@example.com',20,'Coordinator'),(25,'Xavier','Gonzalez','Finance Department','xavier.gonzalez@example.com',19,'Supervisor'),(26,'Waydeed','Redeem','IT Department','waydeed.redeem@example.com',18,'Engineer'),(27,'Zach','Hayes','HR Department','zach.hayes@example.com',10,'Assistant'),(28,'Ava','Price','Finance Department','ava.price@example.com',11,'Auditor'),(29,'Blake','Jenkins','IT Department','blake.jenkins@example.com',12,'Designer'),(30,'Cara','Woods','Finance Department','cara.woods@example.com',13,'Operator'),(31,'Reyansh','Sharma','IT Department','reyansh.sharma@example.com',17,'Programmer'),(32,'Ella','Parker','HR Department','ella.parker@example.com',14,'Recruiter'),(33,'Finn','Edwards','Finance Department','finn.edwards@example.com',10,'Consultant'),(34,'Gina','Stewart','HR Department','gina.stewart@example.com',18,'Specialist'),(35,'Hank','Taylor','Finance Department','hank.taylor@example.com',19,'Coordinator'),(36,'Vikram','Ahluwalia','IT Department','vikram.ahluwalia@example.com',16,'Developer'),(37,'Jake','Harrison','HR Department','jake.harrison@example.com',12,'Trainer'),(38,'Kate','Simmons','Finance Department','kate.simmons@example.com',11,'Analyst'),(39,'Liam','Griffin','HR Department','liam.griffin@example.com',9,'Strategist'),(40,'Molly','West','Finance Department','molly.west@example.com',15,'Supervisor'),(41,'Rikita','Mehta','IT Department','rikita.mehta@example.com',20,'Engineer'),(42,'Olga','Knight','HR Department','olga.knight@example.com',13,'Manager'),(43,'Pete','Lawson','Finance Department','pete.lawson@example.com',14,'Auditor'),(44,'Quincy','Henderson','IT Department','quincy.henderson@example.com',12,'Designer'),(45,'Rose','Armstrong','Finance Department','rose.armstrong@example.com',14,'Operator'),(46,'Johar','Ahuja','IT Department','johar.ahuja@example.com',18,'Developer'),(47,'Tara','Banks','HR Department','tara.banks@example.com',17,'Recruiter'),(48,'Umar','Flores','Finance Department','umar.flores@example.com',10,'Accountant'),(49,'Violet','George','HR Department','violet.george@example.com',11,'Specialist'),(50,'Wesley','Patton','Finance Department','wesley.patton@example.com',16,'Supervisor');
/*!40000 ALTER TABLE `employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leaverequest`
--

DROP TABLE IF EXISTS `leaverequest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leaverequest` (
  `leaveID` int NOT NULL AUTO_INCREMENT,
  `leaveType` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endDate` date NOT NULL,
  `employeeID` int NOT NULL,
  `startDate` date NOT NULL,
  `leaveStatus` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`leaveID`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaverequest`
--

LOCK TABLES `leaverequest` WRITE;
/*!40000 ALTER TABLE `leaverequest` DISABLE KEYS */;
INSERT INTO `leaverequest` VALUES (1,'Sick Leave','2025-01-04',1,'2025-01-03','Approved'),(2,'Vacation Leave','2025-01-15',2,'2025-01-10','Pending'),(3,'Emergency Leave','2025-01-08',3,'2025-01-07','Approved'),(4,'Sick Leave','2025-01-06',4,'2025-01-05','Approved'),(5,'Vacation Leave','2025-01-14',5,'2025-01-12','Pending'),(6,'Sick Leave','2025-01-03',6,'2025-01-02','Rejected'),(7,'Emergency Leave','2025-01-09',7,'2025-01-08','Approved'),(8,'Sick Leave','2025-01-11',8,'2025-01-09','Pending'),(9,'Vacation Leave','2025-01-22',9,'2025-01-20','Approved'),(10,'Sick Leave','2025-01-02',10,'2025-01-01','Approved'),(11,'Vacation Leave','2025-01-20',11,'2025-01-18','Pending'),(12,'Emergency Leave','2025-01-07',12,'2025-01-06','Approved'),(13,'Sick Leave','2025-01-16',13,'2025-01-15','Approved'),(14,'Vacation Leave','2025-01-13',14,'2025-01-11','Approved'),(15,'Emergency Leave','2025-01-10',15,'2025-01-09','Pending'),(16,'Sick Leave','2025-01-04',16,'2025-01-03','Approved'),(17,'Vacation Leave','2025-01-17',17,'2025-01-15','Rejected'),(18,'Emergency Leave','2025-01-08',18,'2025-01-07','Approved'),(19,'Sick Leave','2025-01-05',19,'2025-01-04','Pending'),(20,'Vacation Leave','2025-01-14',20,'2025-01-13','Approved'),(21,'Sick Leave','2025-01-03',21,'2025-01-02','Rejected'),(22,'Vacation Leave','2025-01-12',22,'2025-01-09','Approved'),(23,'Emergency Leave','2025-01-09',23,'2025-01-08','Pending'),(24,'Sick Leave','2025-01-05',24,'2025-01-04','Approved'),(25,'Vacation Leave','2025-01-19',25,'2025-01-17','Rejected'),(26,'Emergency Leave','2025-01-04',26,'2025-01-03','Approved'),(27,'Sick Leave','2025-01-06',27,'2025-01-05','Approved'),(28,'Vacation Leave','2025-01-23',28,'2025-01-20','Pending'),(29,'Emergency Leave','2025-01-09',29,'2025-01-08','Approved'),(30,'Sick Leave','2025-01-02',30,'2025-01-01','Rejected'),(31,'Vacation Leave','2025-01-17',31,'2025-01-15','Approved'),(32,'Emergency Leave','2025-01-07',32,'2025-01-06','Approved'),(33,'Sick Leave','2025-01-12',33,'2025-01-11','Approved'),(34,'Vacation Leave','2025-01-15',34,'2025-01-13','Rejected'),(35,'Emergency Leave','2025-01-04',35,'2025-01-03','Approved'),(36,'Sick Leave','2025-01-06',36,'2025-01-05','Pending'),(37,'Vacation Leave','2025-01-18',37,'2025-01-16','Approved'),(38,'Emergency Leave','2025-01-05',38,'2025-01-04','Pending'),(39,'Sick Leave','2025-01-08',39,'2025-01-07','Approved'),(40,'Vacation Leave','2025-01-14',40,'2025-01-12','Approved'),(41,'Sick Leave','2025-01-07',41,'2025-01-06','Rejected'),(42,'Vacation Leave','2025-01-20',42,'2025-01-18','Approved'),(43,'Emergency Leave','2025-01-10',43,'2025-01-09','Approved'),(44,'Sick Leave','2025-01-06',44,'2025-01-05','Approved'),(45,'Vacation Leave','2025-01-22',45,'2025-01-19','Pending'),(46,'Emergency Leave','2025-01-04',46,'2025-01-03','Approved'),(47,'Sick Leave','2025-01-03',47,'2025-01-02','Approved'),(48,'Vacation Leave','2025-01-13',48,'2025-01-11','Rejected'),(49,'Emergency Leave','2025-01-07',49,'2025-01-06','Approved'),(50,'Sick Leave','2025-01-08',50,'2025-01-07','Pending');
/*!40000 ALTER TABLE `leaverequest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll`
--

DROP TABLE IF EXISTS `payroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll` (
  `payrollID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `hoursWorked` int NOT NULL,
  `ratePerHour` double NOT NULL,
  `salary` double NOT NULL,
  `deductions` double NOT NULL,
  `netPay` double NOT NULL,
  `paymentDate` date NOT NULL,
  PRIMARY KEY (`payrollID`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll`
--

LOCK TABLES `payroll` WRITE;
/*!40000 ALTER TABLE `payroll` DISABLE KEYS */;
INSERT INTO `payroll` VALUES (1,1,'2024-12-01','2024-12-15',80,600,48000,1000,47000,'2024-12-15'),(2,2,'2024-12-01','2024-12-15',85,600,51000,1500,49500,'2024-12-15'),(3,3,'2024-12-01','2024-12-15',70,600,42000,1100,40900,'2024-12-15'),(4,4,'2024-12-16','2024-12-31',90,600,54000,1200,52800,'2024-12-31'),(5,5,'2024-12-16','2024-12-31',75,600,45000,1000,44000,'2024-12-31'),(6,6,'2024-12-16','2024-12-31',85,600,51000,1300,49700,'2024-12-31'),(7,7,'2025-01-01','2025-01-15',80,600,48000,1000,47000,'2025-01-15'),(8,8,'2025-01-01','2025-01-15',85,600,51000,1500,49500,'2025-01-15'),(9,9,'2025-01-01','2025-01-15',70,600,42000,1100,40900,'2025-01-15'),(10,10,'2025-01-16','2025-01-31',90,600,54000,1200,52800,'2025-01-31'),(11,11,'2025-01-16','2025-01-31',75,600,45000,1000,44000,'2025-01-31'),(12,12,'2025-01-16','2025-01-31',85,600,51000,1300,49700,'2025-01-31'),(13,13,'2025-02-01','2025-02-15',80,600,48000,1000,47000,'2025-02-15'),(14,14,'2025-02-01','2025-02-15',85,600,51000,1500,49500,'2025-02-15'),(15,15,'2025-02-01','2025-02-15',70,600,42000,1100,40900,'2025-02-15'),(16,16,'2025-02-16','2025-02-28',90,600,54000,1200,52800,'2025-02-28'),(17,17,'2025-02-16','2025-02-28',75,600,45000,1000,44000,'2025-02-28'),(18,18,'2025-02-16','2025-02-28',85,600,51000,1300,49700,'2025-02-28'),(19,19,'2025-03-01','2025-03-15',80,600,48000,1000,47000,'2025-03-15'),(20,20,'2025-03-01','2025-03-15',85,600,51000,1500,49500,'2025-03-15'),(21,21,'2025-03-01','2025-03-15',70,600,42000,1100,40900,'2025-03-15'),(22,22,'2025-03-16','2025-03-31',90,600,54000,1200,52800,'2025-03-31'),(23,23,'2025-03-16','2025-03-31',75,600,45000,1000,44000,'2025-03-31'),(24,24,'2025-03-16','2025-03-31',85,600,51000,1300,49700,'2025-03-31'),(25,25,'2025-04-01','2025-04-15',80,600,48000,1000,47000,'2025-04-15'),(26,26,'2025-04-01','2025-04-15',85,600,51000,1500,49500,'2025-04-15'),(27,27,'2025-04-01','2025-04-15',70,600,42000,1100,40900,'2025-04-15'),(28,28,'2025-04-16','2025-04-30',90,600,54000,1200,52800,'2025-04-30'),(29,29,'2025-04-16','2025-04-30',75,600,45000,1000,44000,'2025-04-30'),(30,30,'2025-04-16','2025-04-30',85,600,51000,1300,49700,'2025-04-30'),(31,31,'2025-05-01','2025-05-15',80,600,48000,1000,47000,'2025-05-15'),(32,32,'2025-05-01','2025-05-15',85,600,51000,1500,49500,'2025-05-15'),(33,33,'2025-05-01','2025-05-15',70,600,42000,1100,40900,'2025-05-15'),(34,34,'2025-05-16','2025-05-31',90,600,54000,1200,52800,'2025-05-31'),(35,35,'2025-05-16','2025-05-31',75,600,45000,1000,44000,'2025-05-31'),(36,36,'2025-05-16','2025-05-31',85,600,51000,1300,49700,'2025-05-31'),(37,37,'2025-06-01','2025-06-15',80,600,48000,1000,47000,'2025-06-15'),(38,38,'2025-06-01','2025-06-15',85,600,51000,1500,49500,'2025-06-15'),(39,39,'2025-06-01','2025-06-15',70,600,42000,1100,40900,'2025-06-15'),(40,40,'2025-06-16','2025-06-30',90,600,54000,1200,52800,'2025-06-30'),(41,41,'2025-06-16','2025-06-30',75,600,45000,1000,44000,'2025-06-30'),(42,42,'2025-06-16','2025-06-30',85,600,51000,1300,49700,'2025-06-30'),(43,43,'2025-07-01','2025-07-15',80,600,48000,1000,47000,'2025-07-15'),(44,44,'2025-07-01','2025-07-15',85,600,51000,1500,49500,'2025-07-15'),(45,45,'2025-07-01','2025-07-15',70,600,42000,1100,40900,'2025-07-15'),(46,46,'2025-07-16','2025-07-31',90,600,54000,1200,52800,'2025-07-31'),(47,47,'2025-07-16','2025-07-31',75,600,45000,1000,44000,'2025-07-31'),(48,48,'2025-07-16','2025-07-31',85,600,51000,1300,49700,'2025-07-31'),(49,49,'2025-08-01','2025-08-15',80,600,48000,1000,47000,'2025-08-15'),(50,50,'2025-08-01','2025-08-15',85,600,51000,1500,49500,'2025-08-15');
/*!40000 ALTER TABLE `payroll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report` (
  `reportID` int NOT NULL,
  `reportType` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateGenerated` date NOT NULL,
  `generatedBy` int NOT NULL,
  PRIMARY KEY (`reportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report`
--

LOCK TABLES `report` WRITE;
/*!40000 ALTER TABLE `report` DISABLE KEYS */;
/*!40000 ALTER TABLE `report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usercredentials`
--

DROP TABLE IF EXISTS `usercredentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usercredentials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employeeID` int DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usercredentials`
--

LOCK TABLES `usercredentials` WRITE;
/*!40000 ALTER TABLE `usercredentials` DISABLE KEYS */;
INSERT INTO `usercredentials` VALUES (1,1,'johndoe','password123'),(2,2,'janesmith','securePass!'),(3,3,'blixforyasen','blix2024'),(4,4,'jericolim','jerico@123'),(5,5,'jessymapanao','mapanao18'),(6,6,'giancolinares','gian@dev'),(7,7,'franzenatividad','franze#hr'),(8,8,'hannipham','hanni2024'),(9,9,'henrywalker','henry@mark'),(10,10,'ivyhall','ivy#sup'),(11,11,'pajeetpatel','pajeet@17'),(12,12,'karenyoung','karen!rec'),(13,13,'leoking','leo#audit'),(14,14,'miascott','mia2025'),(15,15,'nathanadams','nathan@ops'),(16,16,'rasheedagarwal','rasheed2023'),(17,17,'paulmitchell','paul#train'),(18,18,'quinnperez','quinn#acc'),(19,19,'rachelsanchez','rachel@mark'),(20,20,'samuelmorgan','samuel18'),(21,21,'arammojtabai','aram#consult'),(22,22,'ulyssesbell','ulysses@mgr'),(23,23,'veraramirez','vera@analyst'),(24,24,'walterfoster','walter2024'),(25,25,'xaviergonzalez','xavier@ops'),(26,26,'waydeedredeem','waydeed#eng'),(27,27,'zachhayes','zach2025'),(28,28,'avaprice','ava@audit'),(29,29,'blakejenkins','blake2024'),(30,30,'carawoods','cara@ops'),(31,31,'reyanshsharma','reyansh@prog'),(32,32,'ellaparker','ella@hr'),(33,33,'finnedwards','finn2025'),(34,34,'ginastewart','gina@spec'),(35,35,'hanktaylor','hank2024'),(36,36,'vikramahluwalia','vikram#dev'),(37,37,'jakeharrison','jake@train'),(38,38,'katesimmons','kate@analyst'),(39,39,'liamgriffin','liam@strat'),(40,40,'mollywest','molly@ops'),(41,41,'rikitamehta','rikita2023'),(42,42,'olgaknight','olga@mgr'),(43,43,'petelawson','pete@audit'),(44,44,'quincyhenderson','quincy2024'),(45,45,'rosearmstrong','rose@ops'),(46,46,'joharahuja','johar@dev'),(47,47,'tarabanks','tara@hr'),(48,48,'umarflores','umar@acc'),(49,49,'violetgeorge','violet2025'),(50,50,'wesleypatton','wesley@ops');
/*!40000 ALTER TABLE `usercredentials` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-01-04 10:36:10
