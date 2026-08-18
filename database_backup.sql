-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: skillbridge_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  PRIMARY KEY (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES ('Admin','admin@skillbridge.com');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `certificate_name` varchar(150) DEFAULT NULL,
  `issuer` varchar(150) DEFAULT NULL,
  `certificate_file` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  PRIMARY KEY (`certificate_id`),
  KEY `Email` (`Email`),
  CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company` (
  `Email` varchar(100) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `companytype` varchar(100) NOT NULL,
  `contactPersonName` varchar(100) NOT NULL,
  `contactNumber` varchar(17) NOT NULL,
  `website` varchar(200) NOT NULL,
  `location` varchar(100) NOT NULL,
  `Status` varchar(100) NOT NULL DEFAULT 'Unverified',
  PRIMARY KEY (`Email`),
  KEY `Email` (`Email`),
  CONSTRAINT `company_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company`
--

LOCK TABLES `company` WRITE;
/*!40000 ALTER TABLE `company` DISABLE KEYS */;
INSERT INTO `company` VALUES ('hr@company.com ','HR Pvt LTD','IT','Nadeeka Munasingha','0772343234','www.hr.com','hr pvt ltd,colombo 7','Verify');
/*!40000 ALTER TABLE `company` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complain`
--

DROP TABLE IF EXISTS `complain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `discription` text NOT NULL,
  `priority` enum('LOW','MEDIUM','HIGH','URGENT') NOT NULL DEFAULT 'MEDIUM',
  `status` enum('PENDING','IN_REVIEW','DISMISSED','RESOLVED') NOT NULL DEFAULT 'PENDING',
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  CONSTRAINT `complain_ibfk_1` FOREIGN KEY (`email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complain`
--

LOCK TABLES `complain` WRITE;
/*!40000 ALTER TABLE `complain` DISABLE KEYS */;
INSERT INTO `complain` VALUES (1,'2024is058@stu.ucsc.cmb.ac.lk','System Login Failure','I have been trying to log into my account since yesterday, but I keep getting an \'Invalid Credentials\' error. The password reset link is also not working. Please look into this as soon as possible','MEDIUM','PENDING','2026-08-05 05:20:48','2026-08-05 05:20:48'),(2,'admin@skillbridge.com','Payment Gateway Crash','Customers are receiving a 500 Internal Server Error during the checkout process. Because of this, no payments have been processed since this morning. This needs to be resolved immediately.','MEDIUM','DISMISSED','2026-08-05 05:20:48','2026-08-05 05:20:48'),(3,'harithainduwara0205@gmail.com','Contract Violation','Company failed to provide necessary software licenses for the project within the agreed timeframe. This has caused significant delays in our operational workflow','HIGH','DISMISSED','2026-08-05 05:22:51','2026-08-05 05:22:51');
/*!40000 ALTER TABLE `complain` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internship_applications`
--

DROP TABLE IF EXISTS `internship_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internship_applications` (
  `application_id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `applied_date` date DEFAULT curdate(),
  PRIMARY KEY (`application_id`),
  KEY `Email` (`Email`),
  KEY `internship_id` (`internship_id`),
  CONSTRAINT `internship_applications_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  CONSTRAINT `internship_applications_ibfk_2` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internship_applications`
--

LOCK TABLES `internship_applications` WRITE;
/*!40000 ALTER TABLE `internship_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `internship_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internships`
--

DROP TABLE IF EXISTS `internships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `logo_text` varchar(10) DEFAULT NULL,
  `logo_style` varchar(100) DEFAULT NULL,
  `tech_tags` varchar(255) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `deadline` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internships`
--

LOCK TABLES `internships` WRITE;
/*!40000 ALTER TABLE `internships` DISABLE KEYS */;
INSERT INTO `internships` VALUES (1,'UI/UX Designer','TechFlow Solutions','IT Services','','','Figma, Adobe XD','6 Months','Nov 15, 2026'),(2,'Backend Developer Intern','Global Retail','Software Development','GR','background: #dbeafe;','Node.js, PostgreSQL','3 Months','Oct 20, 2026');
/*!40000 ALTER TABLE `internships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `Email` (`Email`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization`
--

DROP TABLE IF EXISTS `organization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization` (
  `Name` varchar(100) NOT NULL,
  `orgtype` varchar(100) NOT NULL,
  `contactPersonName` varchar(100) NOT NULL,
  `contactNumber` varchar(17) NOT NULL,
  `website` varchar(200) NOT NULL,
  `location` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `about` text DEFAULT NULL,
  `linkedin` varchar(200) DEFAULT NULL,
  `twitter` varchar(200) DEFAULT NULL,
  `facebook` varchar(200) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Email`),
  KEY `Email` (`Email`),
  CONSTRAINT `organization_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization`
--

LOCK TABLES `organization` WRITE;
/*!40000 ALTER TABLE `organization` DISABLE KEYS */;
INSERT INTO `organization` VALUES ('Haritha Induwara','University','Haritha Induwara','+94 773423379','www.HI.org.com','Matara,Sri Lanka','harithainduwara0205@gmail.com',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `organization` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portfolio`
--

DROP TABLE IF EXISTS `portfolio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio` (
  `portfolio_id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `project_link` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`portfolio_id`),
  KEY `Email` (`Email`),
  CONSTRAINT `portfolio_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio`
--

LOCK TABLES `portfolio` WRITE;
/*!40000 ALTER TABLE `portfolio` DISABLE KEYS */;
/*!40000 ALTER TABLE `portfolio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `organization_email` varchar(100) DEFAULT NULL,
  `icon` varchar(50) DEFAULT '?',
  `tag` varchar(100) DEFAULT NULL,
  `tag_class` varchar(50) DEFAULT NULL,
  `tech_stack` varchar(255) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `members` int(11) DEFAULT 1,
  `deadline` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `learning_objectives` text DEFAULT NULL,
  `expected_outcomes` text DEFAULT NULL,
  `difficulty` varchar(30) DEFAULT 'Intermediate',
  `preferred_year` varchar(20) DEFAULT 'Any Year',
  `visibility` varchar(20) DEFAULT 'Public',
  `status` varchar(30) DEFAULT 'open',
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_projects_org` (`organization_email`),
  CONSTRAINT `fk_projects_org` FOREIGN KEY (`organization_email`) REFERENCES `organization` (`Email`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'AI Model Optimization','TechCorp Solutions',NULL,'📊','High Demand','high-demand','Python, TensorFlow, AWS','3 Months',4,'Oct 20, 2026',NULL,NULL,NULL,NULL,NULL,'Intermediate','Any Year','Public','open','2026-08-15 08:17:29'),(2,'Cloud Migration UI/UX','EduConnect',NULL,'🎨','UI/UX','ui-ux','React, Figma, Tailwind','2 Months',2,'Nov 15, 2026',NULL,NULL,NULL,NULL,NULL,'Intermediate','Any Year','Public','open','2026-08-15 08:17:29');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skills` (
  `skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `percentage` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'Pending',
  PRIMARY KEY (`skill_id`),
  KEY `Email` (`Email`),
  CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student` (
  `Email` varchar(100) NOT NULL,
  `University` varchar(100) NOT NULL,
  `year` varchar(30) NOT NULL,
  `degree` varchar(100) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_completion` int(11) DEFAULT 0,
  PRIMARY KEY (`Email`),
  KEY `Email` (`Email`),
  CONSTRAINT `student_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student`
--

LOCK TABLES `student` WRITE;
/*!40000 ALTER TABLE `student` DISABLE KEYS */;
INSERT INTO `student` VALUES ('2023is058@stu.ucsc.cmb.ac.lk','University of Colombo','2023','is','Hasidshs',NULL,NULL,0),('2024is058@stu.ucsc.cmb.ac.lk','University of Colombo','2024','Information System','Haritha Induwara Liyanapathirana',NULL,NULL,9),('harithainduwara05@gmail.com','University of Kalaniya','2024','BS','ssrsjfsh',NULL,NULL,0);
/*!40000 ALTER TABLE `student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_projects`
--

DROP TABLE IF EXISTS `student_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_projects` (
  `Email` varchar(100) NOT NULL,
  `project_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `progress` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Email`,`project_id`),
  KEY `project_id` (`project_id`),
  KEY `Email` (`Email`),
  CONSTRAINT `student_projects_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  CONSTRAINT `student_projects_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_projects`
--

LOCK TABLES `student_projects` WRITE;
/*!40000 ALTER TABLE `student_projects` DISABLE KEYS */;
INSERT INTO `student_projects` VALUES ('2024is058@stu.ucsc.cmb.ac.lk',1,'student',1,'Active');
/*!40000 ALTER TABLE `student_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `universityemails`
--

DROP TABLE IF EXISTS `universityemails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `universityemails` (
  `University` varchar(100) NOT NULL,
  `no` int(11) NOT NULL AUTO_INCREMENT,
  `faculty` varchar(100) NOT NULL,
  `emailEx` varchar(100) NOT NULL,
  `Status` varchar(10) NOT NULL DEFAULT 'De-Active',
  `Location` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `universityemails`
--

LOCK TABLES `universityemails` WRITE;
/*!40000 ALTER TABLE `universityemails` DISABLE KEYS */;
INSERT INTO `universityemails` VALUES ('University OF Colombo',14,'School Of Computing','stu.ucsc.cmb.ac.lk','Active','colombo 7');
/*!40000 ALTER TABLE `universityemails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `Email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'De-Active',
  `verification_code` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('2023is058@stu.ucsc.cmb.ac.lk','40bd001563085fc35165329ea1ff5c5ecbdbbeef','student','Active','','2026-08-09 19:17:04'),('2024is058@stu.ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','student','Active','','2026-06-30 18:30:00'),('admin@skillbridge.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','admin','Active','','2026-07-02 18:30:00'),('harithainduwara0205@gmail.com','05c3205838acd90c3e9f96abb9fe2df36dd8687b','organization','Active','','2026-07-04 18:30:00'),('harithainduwara05@gmail.com','123','student','Active','','2026-08-09 19:18:53'),('hr@company.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','company','Active','','2026-07-05 18:30:00');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-15 14:52:28
