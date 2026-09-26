-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2026 at 01:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skillbridge_db`
--
CREATE DATABASE IF NOT EXISTS `skillbridge_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `skillbridge_db`;

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_merged
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
  `profile_image` varchar(100) NOT NULL,
  `contactNumber` varchar(15) NOT NULL,
  PRIMARY KEY (`Email`),
  CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES ('Sarath Kumara','admin.sarath@skillbridge.lk','admin_default.png','0779876543'),('AdminHI','skillbridge62@gmail.com','admin_1787125323_6a855e4b16fe8.jpeg','0771234560');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
INSERT INTO `certificates` VALUES (1,'2024is058@stu.ucsc.cmb.ac.lk','AWS Certified Cloud Practitioner','Amazon Web Services','cert_aws_haritha.pdf','Approved'),(2,'2024is058@stu.ucsc.cmb.ac.lk','Meta Front-End Developer Professional Certificate','Coursera - Meta','cert_meta_frontend.pdf','Approved'),(3,'2024is001@stu.ucsc.cmb.ac.lk','Deep Learning Specialization','DeepLearning.AI','cert_deeplearning.pdf','Approved'),(4,'2024is015@stu.ucsc.cmb.ac.lk','Google UX Design Professional Certificate','Coursera - Google','cert_google_ux.pdf','Approved'),(5,'2024is032@stu.ucsc.cmb.ac.lk','CompTIA Security+ Certification','CompTIA','cert_security_plus.pdf','Pending'),(6,'2024is044@stu.ucsc.cmb.ac.lk','Docker Certified Associate','Docker Inc.','cert_docker.pdf','Approved'),(7,'2024is091@stu.ucsc.cmb.ac.lk','Associate Android Developer','Google','cert_android_sachini.pdf','Approved'),(8,'2024is091@stu.ucsc.cmb.ac.lk','Flutter & Dart - The Complete Guide','Udemy','cert_flutter_sachini.pdf','Approved');
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
  `profile_img` varchar(255) NOT NULL,
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
INSERT INTO `company` VALUES ('careers@virtusa.com','Virtusa Sri Lanka','IT Consulting & Services','Rohan Silva','0112498000','www.virtusa.com','Orion City, Colombo 09','Verify','virtusa_logo.png'),('hr@company.com','HR Solutions Pvt Ltd','IT & Software Services','Nadeeka Munasingha','0772343234','www.hrsolutions.lk','Colombo 07, Sri Lanka','Verify','company_1788438171_6a99669b20531.jpeg'),('recruitment@wso2.com','WSO2 Lanka (Pvt) Ltd','Software & Middleware','Ayesh Perera','0112145345','www.wso2.com','Bauddhaloka Mawatha, Colombo 04','Verify','wso2_logo.png'),('talent@ifs.com','IFS R&D International','Enterprise Software','Chamari Senanayake','0112364400','www.ifs.com','Orion City, Colombo 09','Verify','ifs_logo.png');
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
  `category` varchar(50) DEFAULT 'Technical',
  `discription` text NOT NULL,
  `priority` enum('LOW','MEDIUM','HIGH','URGENT') NOT NULL DEFAULT 'MEDIUM',
  `status` enum('PENDING','IN_REVIEW','DISMISSED','RESOLVED') NOT NULL DEFAULT 'PENDING',
  `resolution_notes` text DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  CONSTRAINT `complain_ibfk_1` FOREIGN KEY (`email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complain`
--

LOCK TABLES `complain` WRITE;
/*!40000 ALTER TABLE `complain` DISABLE KEYS */;
INSERT INTO `complain` VALUES (1,'2024is058@stu.ucsc.cmb.ac.lk','Issue with certificate upload','Technical','Uploaded certificate PDF file size exceeds 5MB and produces an error without clear instructions.','MEDIUM','DISMISSED','Resolved by Administrator via Quick Action.','2026-08-22 04:45:00','2026-09-26 18:45:43'),(2,'hr@company.com','Internship deadline display timezone bug','Technical','The internship post deadline date displays in UTC instead of Sri Lanka Standard Time (+05:30).','HIGH','DISMISSED','Resolved by Administrator via Quick Action.','2026-08-25 04:00:00','2026-09-26 18:45:34'),(3,'2024is001@stu.ucsc.cmb.ac.lk','Profile image avatar caching','Technical','Updated profile picture did not immediately reflect on dashboard until browser cache was cleared.','HIGH','RESOLVED','Resolved by Administrator via Quick Action.','2026-08-28 11:15:00','2026-09-26 18:46:35'),(4,'careers@virtusa.com','Applicant notification delay','Organization','Instant notification email for incoming student applications was delayed by 30 minutes during peak hours.','HIGH','IN_REVIEW','Resolved by Administrator via Quick Action.','2026-08-29 05:40:00','2026-09-26 19:29:26'),(5,'2024is015@stu.ucsc.cmb.ac.lk','Team chat attachment limits','Technical','Students are unable to send design preview attachments larger than 2MB in student project chat.','MEDIUM','DISMISSED','Resolved by Administrator via Quick Action.','2026-09-01 08:50:00','2026-09-26 18:45:36'),(6,'2024is032@stu.ucsc.cmb.ac.lk','Unable to upload internship application document','Technical','When attempting to submit a CV for the Cloud Engineering internship, the upload component fails after 30 seconds with network timeout.','HIGH','RESOLVED','Dismissed by Administrator as non-actionable.','2026-09-18 04:50:00','2026-09-26 18:46:14'),(7,'foss@sliit.lk','Inaccurate project duration specified by mentor','Organization','The project duration listed on the platform was 3 months, but partner company modified milestone schedule to 6 months without prior discussion.','MEDIUM','DISMISSED','Resolved by Administrator via Quick Action.','2026-09-19 08:45:00','2026-09-26 16:12:39'),(8,'2024is044@stu.ucsc.cmb.ac.lk','Discrepancy in student evaluation score','Academic','Academic evaluation report submitted by external supervisor shows missing rubric breakdown for sprint 3.','LOW','DISMISSED',NULL,'2026-09-15 02:30:00','2026-09-26 18:45:40'),(9,'2024is078@stu.ucsc.cmb.ac.lk','Password reset verification email not received','Technical','Password reset link sent to official university student inbox does not arrive within the 15-minute token expiry window.','HIGH','IN_REVIEW','Resolved by Administrator via Quick Action.','2026-09-22 06:00:00','2026-09-26 18:46:08'),(10,'recruitment@wso2.com','Company profile picture upload failing','Technical','Company brand logo in PNG format fails with validation error even though dimensions are 400x400.','LOW','DISMISSED','Resolved by Administrator via Quick Action.','2026-09-23 04:10:00','2026-09-26 18:45:46'),(11,'talent@ifs.com','Applicant absent for scheduled technical interview','User Conduct','Applicant confirmed slot for interview on Friday 3:00 PM but failed to join the video session without notice.','MEDIUM','DISMISSED','Resolved by Administrator via Quick Action.','2026-09-24 10:30:00','2026-09-25 16:27:09'),(12,'harithainduwara05@gmail.com','Project milestone review overdue by supervisor','Project & Milestones','Final deliverable for AI Research Project was submitted 14 days ago and supervisor review is pending past SLA deadline.','HIGH','DISMISSED','Duplicate submission resolved in ticket CMP-0005','2026-09-25 03:00:00','2026-09-26 19:22:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (1,'Kavinda Jayasundara','kavinda.j@gmail.com','Partnership Inquiry for University Hackathon','Hello SkillBridge team, We would like to collaborate with SkillBridge for our upcoming annual university hackathon. Please get in touch with us.','2026-08-21 03:42:00'),(2,'Ayesha Ranatunga','ayesha.r@outlook.com','Question about student verification process','Hi team, how long does it take for a newly registered student institutional email to be verified by campus admin?','2026-08-24 10:10:00'),(3,'Supun Madushanka','supun.m@techcorp.lk','Employer Onboarding assistance needed','We are interested in listing multiple software engineering internship slots on your portal. Kindly send the employer handbook and terms.','2026-08-27 12:35:00'),(4,'Chamika Dissanaike','chamika.d@gmail.com','Feedback on SkillBridge platform UI','The dark mode and responsive layout on the dashboard are fantastic! Great job on the user experience.','2026-09-02 06:00:00'),(5,'Haritha Induwara','harithainduwara05@gmail.com','Registration Problem','sdfsjdfsgd','2026-09-25 05:44:37');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internship_applications`
--

LOCK TABLES `internship_applications` WRITE;
/*!40000 ALTER TABLE `internship_applications` DISABLE KEYS */;
INSERT INTO `internship_applications` VALUES (1,'2024is058@stu.ucsc.cmb.ac.lk',1,'Shortlisted','2026-08-25'),(2,'2024is058@stu.ucsc.cmb.ac.lk',3,'Under Review','2026-08-28'),(3,'2024is001@stu.ucsc.cmb.ac.lk',2,'Accepted','2026-08-22'),(4,'2024is001@stu.ucsc.cmb.ac.lk',6,'Shortlisted','2026-08-26'),(5,'2024is015@stu.ucsc.cmb.ac.lk',1,'Accepted','2026-08-20'),(6,'2024is032@stu.ucsc.cmb.ac.lk',4,'Pending','2026-09-01'),(7,'2024is044@stu.ucsc.cmb.ac.lk',5,'Under Review','2026-08-30');
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
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `academic_year` varchar(50) DEFAULT NULL,
  `experience_level` varchar(50) DEFAULT NULL,
  `vacancies` int(11) DEFAULT NULL,
  `internship_type` varchar(50) DEFAULT NULL,
  `work_mode` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `paid_status` varchar(20) DEFAULT NULL,
  `stipend` decimal(10,2) DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `supporting_document` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internships`
--

LOCK TABLES `internships` WRITE;
/*!40000 ALTER TABLE `internships` DISABLE KEYS */;
INSERT INTO `internships` VALUES (1,'UI/UX Designer Intern','HR Solutions Pvt Ltd','IT Services','TF','background: #e0e7ff; color: #4338ca;','Figma, Adobe XD, CSS','6 Months','Nov 15, 2026',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'Backend Developer Intern','HR Solutions Pvt Ltd','Software Development','GR','background: #dbeafe; color: #1e40af;','Node.js, PostgreSQL, Docker','3 Months','Oct 20, 2026',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'Associate Software Engineer Intern','Virtusa Sri Lanka','IT Consulting','VIR','background: #fee2e2; color: #b91c1c;','Java, Spring Boot, React, AWS','6 Months','Dec 01, 2026',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,'Cloud Integration Intern','WSO2 Lanka (Pvt) Ltd','Software & Middleware','WSO2','background: #ffedd5; color: #c2410c;','Ballerina, Microservices, Kubernetes','6 Months','Nov 30, 2026',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,'Full-Stack Developer Intern','IFS R&D International','Enterprise Software','IFS','background: #f3e8ff; color: #7e22ce;','Angular, C#, .NET Core, Oracle','6 Months','Dec 15, 2026',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,'Mobile Application Developer Intern','HR Solutions Pvt Ltd','IT Solutions','HR','background: #dcfce7; color: #15803d;','Flutter, Dart, Firebase, REST APIs','3 Months','Oct 31, 2026',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
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
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'2024is058@stu.ucsc.cmb.ac.lk','Application Shortlisted','Congratulations! Your application for UI/UX Designer Intern has been shortlisted.','application','Read','2026-08-26 04:30:00'),(2,'2024is058@stu.ucsc.cmb.ac.lk','New Project Invitation','You have been invited to collaborate on the project \"Smart Campus Energy Monitor\".','project','Unread','2026-09-01 09:00:00'),(3,'2024is001@stu.ucsc.cmb.ac.lk','Application Accepted','Global Retail has accepted your application for Backend Developer Intern!','application','Read','2026-08-24 10:50:00'),(4,'hr@company.com','New Candidate Application','Haritha Induwara has applied for the Mobile Application Developer Intern position.','candidate','Unread','2026-08-29 03:45:00'),(5,'careers@virtusa.com','Profile Verification Complete','Your company profile has been verified by SkillBridge Administration.','system','Read','2026-08-21 05:30:00'),(6,'skillbridge62@gmail.com','New Organization Registration','Organization \"IEEE Student Branch UCSC\" has registered and is pending review.','admin','Unread','2026-09-02 03:15:00'),(7,'foss@sliit.lk','New Project Proposal Received','A new student proposal has been submitted for your API Integration project.','proposal','Unread','2026-09-24 20:31:41'),(8,'foss@sliit.lk','New Application Received','A student has applied for one of your organization projects.','candidate','Unread','2026-09-24 20:31:41'),(9,'foss@sliit.lk','New Team Member Joined','A new student has joined the Project Phoenix team.','team','Read','2026-09-24 20:31:41'),(10,'foss@sliit.lk','Project Progress Updated','The Smart Campus Energy Monitor team has submitted a new progress update.','project','Read','2026-09-24 20:31:41'),(11,'foss@sliit.lk','Proposal Review Reminder','There are pending student proposals waiting for your review.','proposal','Read','2026-09-24 20:31:41'),(12,'foss@sliit.lk','Team Progress Updated','The University Research Collaboration Platform team has updated their progress.','team','Read','2026-09-24 20:31:41'),(13,'foss@sliit.lk','New Project Activity','There has been new activity on one of your organization projects.','project','Read','2026-09-24 20:31:41'),(14,'foss@sliit.lk','Project Deadline Approaching','The Smart Agriculture Monitoring System project deadline is approaching.','project','Read','2026-09-24 20:31:41'),(15,'foss@sliit.lk','Application Status Updated','A student application has been updated and is ready for your review.','candidate','Read','2026-09-24 20:31:41'),(16,'foss@sliit.lk','Team Assignment Updated','A student has been assigned to one of your organization projects.','team','Read','2026-09-24 20:31:41'),(20,'foss@sliit.lk','Project Put On Hold','Your project \"Smart Clinic Queue & Appointment System\" was put on hold by the Admin because it breaks 4 project posting rules. Open Manage Projects to see the reason and fix it.','project','Unread','2026-09-24 09:29:29'),(22,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Not Accepted','SLIIT FOSS Community reviewed your proposal for \"AI Model Optimization\" and did not accept it. Reason: Your skills don’t match the project requirements.','project','Unread','2026-09-25 18:00:28'),(23,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Model Optimization\".','project','Unread','2026-09-25 18:20:11'),(24,'2024is015@stu.ucsc.cmb.ac.lk','Proposal Not Accepted','SLIIT FOSS Community reviewed your proposal for \"Cloud Migration UI/UX\" and did not accept it. Reason: Your skills don’t match the project requirements.','project','Unread','2026-09-25 18:20:20'),(25,'2024is001@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"Smart Library Assistant\".','project','Unread','2026-09-25 18:20:36'),(26,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Model Optimization\".','project','Unread','2026-09-25 18:21:01'),(27,'2024is015@stu.ucsc.cmb.ac.lk','Proposal Not Accepted','SLIIT FOSS Community reviewed your proposal for \"Cloud Migration UI/UX\" and did not accept it. Reason: Your skills don’t match the project requirements.','project','Unread','2026-09-25 18:21:12'),(28,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Model Optimization\".','project','Unread','2026-09-25 18:21:44'),(29,'2024is015@stu.ucsc.cmb.ac.lk','Proposal Not Accepted','SLIIT FOSS Community reviewed your proposal for \"Cloud Migration UI/UX\" and did not accept it. Reason: Your skills don’t match the project requirements.','project','Unread','2026-09-25 18:23:20'),(30,'2024is001@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"Smart Library Assistant\".','project','Unread','2026-09-25 18:25:06'),(31,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Model Optimization\".','project','Unread','2026-09-25 18:31:21'),(32,'2024is015@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"Cloud Migration UI/UX\".','project','Unread','2026-09-25 18:31:36'),(33,'2024is001@stu.ucsc.cmb.ac.lk','Proposal Not Accepted','SLIIT FOSS Community reviewed your proposal for \"Smart Library Assistant\" and did not accept it. Reason: Your skills don’t match the project requirements.','project','Unread','2026-09-25 18:31:43'),(34,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Model Optimization\".','project','Unread','2026-09-25 18:32:48'),(35,'2024is015@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"Cloud Migration UI/UX\".','project','Unread','2026-09-25 18:37:38'),(36,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Model Optimization\".','project','Unread','2026-09-25 18:37:56'),(37,'2024is015@stu.ucsc.cmb.ac.lk','Proposal Not Accepted','SLIIT FOSS Community reviewed your proposal for \"Cloud Migration UI/UX\" and did not accept it. Reason: Your skills don’t match the project requirements.','project','Unread','2026-09-25 18:38:04'),(38,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Model Optimization\".','project','Unread','2026-09-25 18:39:51'),(39,'2024is015@stu.ucsc.cmb.ac.lk','Proposal Not Accepted','SLIIT FOSS Community reviewed your proposal for \"Cloud Migration UI/UX\" and did not accept it. Reason: Your skills don’t match the project requirements.','project','Unread','2026-09-25 18:40:00'),(40,'2024is001@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"Smart Library Assistant\".','project','Unread','2026-09-25 18:40:10'),(41,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Chatbot for Student Support\". Message: Great proposal! Welcome to the project. We will add you to a team soon.','project','Unread','2026-09-26 04:34:54'),(42,'2024is015@stu.ucsc.cmb.ac.lk','Proposal Not Accepted','SLIIT FOSS Community reviewed your proposal for \"Smart Agriculture Monitoring System\" and did not accept it. Reason: Your skills don’t match the project requirements.','project','Unread','2026-09-26 04:35:04'),(46,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Chatbot for Student Support\". Message: Great proposal! Welcome to the project. We will add you to a team soon.','project','Unread','2026-09-26 15:17:22'),(47,'2024is015@stu.ucsc.cmb.ac.lk','Proposal Not Accepted','SLIIT FOSS Community reviewed your proposal for \"Smart Agriculture Monitoring System\" and did not accept it. Reason: Your skills don’t match the project requirements.','project','Unread','2026-09-26 15:17:29'),(48,'2024is032@stu.ucsc.cmb.ac.lk','Proposal Accepted','SLIIT FOSS Community accepted your proposal for \"AI Chatbot for Student Support\". Message: Great proposal! Welcome to the project. We will add you to a team soon.','project','Unread','2026-09-26 17:11:57'),(49,'admin.sarath@skillbridge.lk','New Contact Inquiry: Registration Problem','From: Haritha Induwara (harithainduwara05@gmail.com)\n\nsdfsjdfsgd','contact_inquiry','Unread','2026-09-25 05:44:37'),(50,'recruitment@wso2.com','Complaint Under Investigation (#CMP-0010)','Admin update regarding \"Company profile picture upload failing\": Resolved by Administrator via Quick Action.','complaint','Unread','2026-09-25 16:43:12'),(51,'harithainduwara05@gmail.com','Complaint Under Investigation (#CMP-0012)','Admin update regarding \"Project milestone review overdue by supervisor\": Hello Haritha, your complaint has been received and our team is working on it.','complaint','Unread','2026-09-25 16:47:22'),(52,'harithainduwara05@gmail.com','Complaint Resolved (#CMP-0012)','Resolution for \"Project milestone review overdue by supervisor\": Hello Haritha, your complaint has been received and our team is working on it.','complaint','Unread','2026-09-25 16:49:41'),(53,'recruitment@wso2.com','Complaint Update (#CMP-0010)','Resolved by Administrator via Quick Action.','complaint','Unread','2026-09-25 16:50:26'),(54,'recruitment@wso2.com','Complaint Resolved (#CMP-0010)','Resolution for \"Company profile picture upload failing\": Resolved by Administrator via Quick Action.','complaint','Unread','2026-09-26 16:07:11'),(55,'foss@sliit.lk','Complaint Under Investigation (#CMP-0007)','Your reported issue (#CMP-0007) has been placed under active investigation by administrators.','complaint','Unread','2026-09-26 16:12:21'),(56,'harithainduwara05@gmail.com','Complaint Update (#CMP-0012)','Hello Haritha, your complaint has been received and our team is working on it.','complaint','Unread','2026-09-26 18:46:02'),(57,'2024is078@stu.ucsc.cmb.ac.lk','Complaint Under Investigation (#CMP-0009)','Admin update regarding \"Password reset verification email not received\": Resolved by Administrator via Quick Action.','complaint','Unread','2026-09-26 18:46:08'),(58,'2024is032@stu.ucsc.cmb.ac.lk','Complaint Resolved (#CMP-0006)','Resolution for \"Unable to upload internship application document\": Dismissed by Administrator as non-actionable.','complaint','Unread','2026-09-26 18:46:14'),(59,'careers@virtusa.com','Complaint Under Investigation (#CMP-0004)','Admin update regarding \"Applicant notification delay\": Resolved by Administrator via Quick Action.','complaint','Unread','2026-09-26 18:46:23'),(60,'2024is001@stu.ucsc.cmb.ac.lk','Complaint Update (#CMP-0003)','Resolved by Administrator via Quick Action.','complaint','Unread','2026-09-26 18:46:29'),(61,'2024is001@stu.ucsc.cmb.ac.lk','Complaint Resolved (#CMP-0003)','Resolution for \"Profile image avatar caching\": Resolved by Administrator via Quick Action.','complaint','Unread','2026-09-26 18:46:35'),(62,'harithainduwara05@gmail.com','Complaint Dismissed (#CMP-0012)','Your complaint \"Project milestone review overdue by supervisor\" was dismissed. Reason: Duplicate submission resolved in ticket CMP-0005','complaint','Unread','2026-09-26 19:22:29'),(63,'careers@virtusa.com','Complaint Update (#CMP-0004)','Your complaint status has been updated to PENDING.','complaint','Unread','2026-09-26 19:29:20'),(64,'careers@virtusa.com','Complaint Under Investigation (#CMP-0004)','Your reported issue (#CMP-0004) has been placed under active investigation by administrators.','complaint','Unread','2026-09-26 19:29:26');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `org_team_members`
--

DROP TABLE IF EXISTS `org_team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `org_team_members` (
  `team_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`team_id`,`Email`),
  KEY `fk_otm_student` (`Email`),
  CONSTRAINT `fk_otm_student` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  CONSTRAINT `fk_otm_team` FOREIGN KEY (`team_id`) REFERENCES `org_teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `org_team_members`
--

LOCK TABLES `org_team_members` WRITE;
/*!40000 ALTER TABLE `org_team_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `org_team_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `org_teams`
--

DROP TABLE IF EXISTS `org_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `org_teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_email` varchar(100) NOT NULL,
  `project_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `leader_email` varchar(100) NOT NULL,
  `skills` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ontrack',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_org_team_name` (`organization_email`,`name`),
  KEY `idx_org_teams_project` (`project_id`),
  CONSTRAINT `fk_org_teams_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `org_teams`
--

LOCK TABLES `org_teams` WRITE;
/*!40000 ALTER TABLE `org_teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `org_teams` ENABLE KEYS */;
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
INSERT INTO `organization` VALUES ('MYDB Club','Student Society','Haritha Induwara','0779063904','https://mydb.org','Colombo, Sri Lanka','2024is052@stu.ucsc.cmb.ac.lk','Student database exploration and open source software initiatives.','https://linkedin.com/company/mydb','','','mydb_logo.png'),('SLIIT FOSS Community','Open Source Community','Tharindu Gamage','0761122334','https://foss.sliit.lk','New Kandy Road, Malabe','foss@sliit.lk','Promoting free and open-source software culture among university undergraduates.','https://linkedin.com/company/sliit-foss','https://twitter.com/sliitfoss','https://facebook.com/sliitfoss',NULL),('IEEE Student Branch UCSC','Academic Society','Kasun Rathnayake','0715551234','https://ieee.ucsc.cmb.ac.lk','UCSC, Reid Avenue, Colombo 07','ieee@ucsc.cmb.ac.lk','Empowering students to innovate and excel in technology and engineering disciplines.','https://linkedin.com/company/ieee-ucsc','https://twitter.com/ieee_ucsc','https://facebook.com/ieeeucsc','ieee_logo.png'),('Rotaract Club of UCSC','Community Service','Sachini Wickramasinghe','0784449876','https://rotaractucsc.org','Colombo 07, Sri Lanka','rotaract@ucsc.cmb.ac.lk','Youth leadership and social community empowerment initiatives across Sri Lanka.','https://linkedin.com/company/rotaract-ucsc','','https://facebook.com/rotaractucsc','rotaract_logo.png');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio`
--

LOCK TABLES `portfolio` WRITE;
/*!40000 ALTER TABLE `portfolio` DISABLE KEYS */;
INSERT INTO `portfolio` VALUES (1,'2024is058@stu.ucsc.cmb.ac.lk','SkillBridge Platform','Modern role-based internship matching and student portfolio management system built with PHP and MySQL.','https://github.com/harithainduwara/Skill_Bridge','portfolio_skillbridge.png'),(2,'2024is058@stu.ucsc.cmb.ac.lk','E-Commerce Microservices','High-performance microservices backend with product catalog, cart, and payment gateway integration.','https://github.com/harithainduwara/ecommerce-microservices','portfolio_ecommerce.png'),(3,'2024is001@stu.ucsc.cmb.ac.lk','Skin Disease Detection with CNN','Deep learning mobile application that detects dermatological conditions from camera photos using Flutter and PyTorch.','https://github.com/kavinduperera/skin-disease-ai','portfolio_skindisease.png'),(4,'2024is015@stu.ucsc.cmb.ac.lk','FinTech Digital Wallet UI/UX','Complete UX research, wireframing, design tokens, and interactive Figma prototyping for digital wallet application.','https://www.behance.net/gallery/fintech-app-redesign','portfolio_fintech.png'),(5,'2024is032@stu.ucsc.cmb.ac.lk','Automated Network Security Scanner','Python CLI tool for security audits, port scanning, and CVE reporting using Nmap and Shodan APIs.','https://github.com/sahanw/vuln-scanner','portfolio_scanner.png'),(6,'2024is044@stu.ucsc.cmb.ac.lk','Real-Time Kubernetes Analytics Dashboard','Kubernetes-deployed dashboard visualizing live streaming server metrics using React, Kafka, and Grafana.','https://github.com/dinithij/k8s-analytics-dashboard','portfolio_k8s.png'),(7,'2024is091@stu.ucsc.cmb.ac.lk','Campus Bus Tracker','Flutter app that shows university shuttle buses on a live map with arrival alerts, built with Firebase Realtime Database.','https://github.com/sachiniw/campus-bus-tracker',NULL),(8,'2024is091@stu.ucsc.cmb.ac.lk','StudyBuddy Flashcards','Offline-first flashcard app with spaced repetition and dark mode, published as an internal beta for classmates.','https://github.com/sachiniw/studybuddy',NULL);
/*!40000 ALTER TABLE `portfolio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_applications`
--

DROP TABLE IF EXISTS `project_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `decided_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_project_student` (`project_id`,`Email`),
  KEY `idx_pa_email` (`Email`),
  CONSTRAINT `fk_pa_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pa_student` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_applications`
--

LOCK TABLES `project_applications` WRITE;
/*!40000 ALTER TABLE `project_applications` DISABLE KEYS */;
INSERT INTO `project_applications` VALUES (5,12,'2024is058@stu.ucsc.cmb.ac.lk','accepted','2026-09-21 07:50:04',NULL),(6,12,'2024is078@stu.ucsc.cmb.ac.lk','pending','2026-09-22 07:50:04',NULL),(7,12,'2024is032@stu.ucsc.cmb.ac.lk','pending','2026-09-23 07:50:04',NULL),(10,6,'2024is044@stu.ucsc.cmb.ac.lk','pending','2026-09-13 07:56:23',NULL),(11,6,'2024is078@stu.ucsc.cmb.ac.lk','pending','2026-09-14 07:56:23',NULL),(12,7,'2024is058@stu.ucsc.cmb.ac.lk','pending','2026-09-15 07:56:23',NULL),(13,7,'2024is015@stu.ucsc.cmb.ac.lk','pending','2026-09-16 07:56:23',NULL),(14,7,'2024is032@stu.ucsc.cmb.ac.lk','pending','2026-09-17 07:56:23',NULL),(15,8,'2024is001@stu.ucsc.cmb.ac.lk','pending','2026-09-18 07:56:23',NULL),(16,8,'2024is058@stu.ucsc.cmb.ac.lk','pending','2026-09-19 07:56:23',NULL),(17,8,'2024is078@stu.ucsc.cmb.ac.lk','pending','2026-09-20 07:56:23',NULL),(18,9,'2024is015@stu.ucsc.cmb.ac.lk','pending','2026-09-21 07:56:23',NULL),(19,9,'2024is032@stu.ucsc.cmb.ac.lk','pending','2026-09-22 07:56:23',NULL),(20,9,'2024is078@stu.ucsc.cmb.ac.lk','pending','2026-09-23 07:56:23',NULL),(21,12,'2024is001@stu.ucsc.cmb.ac.lk','pending','2026-09-24 07:56:23',NULL),(51,9,'2024is044@stu.ucsc.cmb.ac.lk','accepted','2026-09-26 15:49:23','2026-09-26 15:49:23'),(52,6,'2024is032@stu.ucsc.cmb.ac.lk','accepted','2026-09-26 17:11:57','2026-09-26 17:11:57');
/*!40000 ALTER TABLE `project_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_documents`
--

DROP TABLE IF EXISTS `project_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_documents_project` (`project_id`),
  CONSTRAINT `fk_project_documents_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_documents`
--

LOCK TABLES `project_documents` WRITE;
/*!40000 ALTER TABLE `project_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_feedback`
--

DROP TABLE IF EXISTS `project_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_email` varchar(100) NOT NULL,
  `project_id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `technical` decimal(2,1) NOT NULL,
  `communication` decimal(2,1) NOT NULL,
  `teamwork` decimal(2,1) NOT NULL,
  `problem_solving` decimal(2,1) NOT NULL,
  `summary` text NOT NULL,
  `improvements` varchar(500) DEFAULT NULL,
  `shared_with_students` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pf_org` (`organization_email`),
  KEY `fk_pf_project` (`project_id`),
  CONSTRAINT `fk_pf_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_feedback`
--

LOCK TABLES `project_feedback` WRITE;
/*!40000 ALTER TABLE `project_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_feedback` ENABLE KEYS */;
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
  `status` varchar(30) DEFAULT 'reviewing',
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hold_reason` text DEFAULT NULL COMMENT 'Why the Admin put the project on hold',
  `hold_fields` varchar(255) DEFAULT NULL COMMENT 'Comma list of fields to fix, e.g. description,deadline,members',
  `held_by` varchar(100) DEFAULT NULL COMMENT 'Admin email',
  `held_at` datetime DEFAULT NULL,
  `hold_response` text DEFAULT NULL COMMENT 'Organization message to the Admin after fixing',
  `hold_resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_projects_org` (`organization_email`),
  CONSTRAINT `fk_projects_org` FOREIGN KEY (`organization_email`) REFERENCES `organization` (`Email`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'AI Model Optimization','MYDB Club','2024is052@stu.ucsc.cmb.ac.lk','📊','High Demand','high-demand','Python, TensorFlow, AWS','3 Months',4,'Oct 20, 2026','Artificial Intelligence','AI, Machine Learning, TensorFlow, Model Pruning','Optimizing large deep learning transformer models for edge devices and mobile browsers.','Learn quantization, model pruning, ONNX runtime optimization.','Production ready lightweight inference model with <50ms latency.','Intermediate','Any Year','Public','reviewing','2026-08-17 13:46:49',NULL,NULL,NULL,NULL,NULL,NULL),(2,'Cloud Migration UI/UX','MYDB Club','2024is052@stu.ucsc.cmb.ac.lk','🎨','UI/UX','ui-ux','React, Figma, Tailwind','2 Months',2,'Nov 15, 2026','UI/UX Design','Design Systems, User Research, Prototyping','Modernizing legacy university student portal interfaces into accessible, responsive cloud UI.','Master accessible design principles (WCAG 2.1) and component library architecture.','Comprehensive Figma design system and React component kit.','Intermediate','Any Year','Public','reviewing','2026-08-17 13:46:49',NULL,NULL,NULL,NULL,NULL,NULL),(3,'Smart Campus Energy Monitor','IEEE Student Branch UCSC','ieee@ucsc.cmb.ac.lk','⚡','IoT & Hardware','iot-hardware','ESP32, MQTT, Node.js, Grafana','4 Months',5,'Dec 10, 2026','Internet of Things','IoT, Smart Campus, Green Tech, Sensors','Building IoT power meters across faculty computer labs to track and minimize electricity waste in real-time.','Gain hands-on experience with microcontroller programming, MQTT broker protocols, and sensor calibration.','Operational live campus energy dashboard and automated alert system for abnormal power surges.','Advanced','3rd Year','Public','reviewing','2026-08-25 08:30:00',NULL,NULL,NULL,NULL,NULL,NULL),(4,'Open Source Blood Donation Portal','Rotaract Club of UCSC','rotaract@ucsc.cmb.ac.lk','❤️','Social Good','community-impact','PHP, MySQL, Bootstrap, Twilio','2 Months',3,'Nov 01, 2026','Web Development','Community, Healthcare, SMS Gateway, Emergency Alerts','A streamlined digital platform connecting volunteer blood donors with regional hospitals during critical shortages.','Build secure role-based portals, integrate SMS alerts, and implement geolocation search.','Fully tested web application deployed for nationwide donor registration drives.','Beginner','1st Year','Public','reviewing','2026-08-28 05:00:00',NULL,NULL,NULL,NULL,NULL,NULL),(5,'FOSS Contributor Leaderboard','SLIIT FOSS Community','foss@sliit.lk','🚀','Open Source','open-source','Go, GitHub GraphQL API, Next.js','3 Months',4,'Nov 25, 2026','DevOps & Tooling','GitHub API, Go, Open Source, Hacktoberfest','Gamified leaderboard tracking pull requests, commits, and reviews of university students contributing to open source repositories.','Work with GraphQL APIs, asynchronous queue processing in Go, and modern frontend dashboards.','Automated ranking system used for annual Hacktoberfest and code sprint awards.','Intermediate','2nd Year','Public','reviewing','2026-09-01 10:30:00',NULL,NULL,NULL,NULL,NULL,NULL),(6,'AI Chatbot for Student Support','SLIIT FOSS Community','foss@sliit.lk','🤖','AI','ai-ml','Python, NLP, Flask','2 Months',4,'Oct 14, 2026','AI / Machine Learning','Python, NLP, Flask, Chatbot','A conversational chatbot that answers common student queries about courses, deadlines and campus services.','Build intent-classification pipelines and integrate an NLP model into a live Flask backend.','A deployed chatbot handling FAQs for the student portal.','Intermediate','Any Year','Public','inprogress','2026-09-23 04:30:00',NULL,NULL,NULL,NULL,NULL,NULL),(7,'Mobile Attendance Tracker','SLIIT FOSS Community','foss@sliit.lk','📱','Mobile','mobile-dev','Flutter, Firebase','2 Months',2,'Oct 09, 2026','Mobile Development','Flutter, Firebase, QR Code','A mobile app that lets lecturers mark attendance via QR code scans, synced live to Firebase.','Learn cross-platform Flutter development and real-time Firebase database sync.','A working attendance app tested in at least one live class.','Beginner','Any Year','Public','inprogress','2026-09-22 04:30:00',NULL,NULL,NULL,NULL,NULL,NULL),(8,'Portfolio Website Builder','SLIIT FOSS Community','foss@sliit.lk','💼','Web','web-dev','React, Tailwind CSS','1 Month',3,'Oct 19, 2026','Web Development','React, Tailwind CSS, Portfolio','A drag-and-drop builder that lets students generate a personal portfolio site from a template.','Practice component-driven React development and utility-first CSS with Tailwind.','A reusable portfolio template students can fork and deploy.','Beginner','Any Year','Public','inprogress','2026-09-21 04:30:00',NULL,NULL,NULL,NULL,NULL,NULL),(9,'Campus Event Management System','SLIIT FOSS Community','foss@sliit.lk','🎪','Web','web-dev','Laravel, MySQL','2 Months',3,'Sep 29, 2026','Web Development','Laravel, MySQL, Events','A system for clubs to publish campus events, manage RSVPs and check in attendees at the door.','Work with Laravel\'s MVC structure, MySQL relational design, and RSVP/ticketing logic.','A working event registration and check-in system used for one real campus event.','Intermediate','2nd Year','Public','completed','2026-09-20 04:30:00',NULL,NULL,NULL,NULL,NULL,NULL),(10,'University Research Collaboration Platform','IEEE Student Branch UCSC','ieee@ucsc.cmb.ac.lk','🔬','Research & Innovation','research','React, Node.js, PostgreSQL, Docker','4 Months',4,'2027-01-15','Web Development','Research, Collaboration, PostgreSQL, REST API','A collaborative platform for university students and researchers to manage research projects, tasks, documents and team collaboration.','Learn REST API development, database design, authentication and collaborative system development.','Working research collaboration platform with project management and document sharing features.','Intermediate','2nd Year','Public','reviewing','2026-09-24 13:20:56',NULL,NULL,NULL,NULL,NULL,NULL),(11,'Smart Agriculture Monitoring System','SLIIT FOSS Community','foss@sliit.lk','🌱','IoT & Smart Systems','iot-hardware','IoT, Smart Agriculture, Sensors, ESP32, Firebase','3 Weeks',6,'2026-12-20','Web Development','IoT, Smart Agriculture, Sensors, ESP32, Firebase','An IoT-based agricultural monitoring system that collects soil moisture, temperature and humidity data and displays real-time information through a web dashboard.','Learn IoT sensor integration, MQTT communication, cloud data storage and real-time monitoring.','Functional smart agriculture monitoring system with a real-time sensor dashboard and automated alerts.','Advanced','Any Year','Public','reviewing','2026-09-24 13:20:56',NULL,NULL,NULL,NULL,NULL,NULL),(12,'Campus Ride-Sharing App','SLIIT FOSS Community','foss@sliit.lk','🚗','Mobile','mobile-dev','React Native, Node.js','2 Months',3,'Nov 05, 2026','Mobile Development','React Native, Node.js, Maps API','A carpooling app for students to share rides to and from campus.','Build a React Native app with live location tracking and a Node.js matching backend.','A working ride-matching prototype for campus commutes.','Intermediate','Any Year','Public','rejected','2026-09-18 04:30:00',NULL,NULL,NULL,NULL,NULL,NULL),(15,'Crypto Trading Signal Bot','SLIIT FOSS Community','foss@sliit.lk','🤖','Finance','fintech','Python, Binance API, Telegram Bot API','6 Weeks',3,'2026-11-30','FinTech','Python, Binance API, Telegram Bot API','A bot that reads crypto market data and sends buy/sell signals to a Telegram group.','Working with REST APIs, basic data analysis, bot development.','A working Telegram bot that posts automated trading signals.','Intermediate','Any Year','Public','rejected','2026-09-23 18:29:00',NULL,NULL,NULL,NULL,NULL,NULL),(17,'Smart Clinic Queue & Appointment System','SLIIT FOSS Community','foss@sliit.lk','🏥','HealthTech','web-dev','Laravel, MySQL, Twilio SMS, Chart.js','12 Weeks',4,'2026-11-15','Web Development','Laravel, MySQL, Twilio SMS, Chart.js','We are partnering with Malabe Community Clinic to replace their paper token system with an online queue and appointment platform. Patients can book a time slot, get an SMS when their turn is close, and doctors can see the live queue on a dashboard.\r\n\r\nStudents will work directly with the clinic\'s REAL patient records (names, NIC numbers, phone numbers and medical history) exported from their current system, so the app can be tested with real data from day one.\r\n\r\nSelected students must pay a registration fee of LKR 2,500 each to cover server and SMS costs.\r\n\r\nFor quick communication please contact our coordinator directly on WhatsApp 077 123 4567 or kasun.foss.coordinator@gmail.com instead of using SkillBridge messages.','Build a full-stack Laravel application, design a real-time queue system, integrate SMS notifications with Twilio, and create analytics dashboards with Chart.js.','A production-ready queue and appointment system deployed at the clinic.\r\n\r\nAll source code, designs and documents will become the sole property of SLIIT FOSS Community. Students may NOT show this project, screenshots or code in their portfolio, CV or GitHub.','Advanced','Year 3','Public','hold','2026-09-21 18:29:00','This project looks valuable, but it breaks 4 SkillBridge project posting rules. Please fix them and request activation again.\n\n1) Real personal / medical data (Data Privacy rule)\nStudents cannot be given real patient records, NIC numbers or medical history. Use anonymised or sample (dummy) data only. Real data may only be used later by the clinic\'s own staff.\n\n2) Fees from students (Free Participation rule)\nProjects on SkillBridge must be free for students. Remove the LKR 2,500 registration fee. If there are server or SMS costs, the organization must cover them.\n\n3) Contact outside the platform (Communication rule)\nPersonal WhatsApp numbers and personal email addresses are not allowed in project posts. All communication with students must go through SkillBridge.\n\n4) Portfolio rights (Student Learning rule)\nStudents must be allowed to show their work in their portfolio / CV. You can keep ownership of the code, but remove the line that stops students from showing it. You may ask them to hide sensitive parts.','description,expected_outcomes','skillbridge62@gmail.com','2026-09-24 14:59:29',NULL,NULL);
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
  `initial_experience` int(11) DEFAULT 0,
  `percentage` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`skill_id`),
  KEY `Email` (`Email`),
  CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES (1,'2024is058@stu.ucsc.cmb.ac.lk','React','Frontend','Advanced',2,80,'2026-09-22 08:00:00'),(2,'2024is058@stu.ucsc.cmb.ac.lk','PHP','Backend','Expert',3,95,'2026-09-22 08:00:00'),(3,'2024is058@stu.ucsc.cmb.ac.lk','MySQL','Database','Advanced',2,80,'2026-09-22 08:00:00'),(4,'2024is058@stu.ucsc.cmb.ac.lk','Docker','Cloud & DevOps','Intermediate',1,60,'2026-09-22 08:00:00'),(5,'2024is078@stu.ucsc.cmb.ac.lk','Python','Programming','Expert',3,95,'2026-09-22 08:00:00'),(6,'2024is078@stu.ucsc.cmb.ac.lk','Flutter','Mobile Development','Advanced',2,80,'2026-09-22 08:00:00'),(7,'2024is078@stu.ucsc.cmb.ac.lk','PyTorch','AI & Machine Learning','Intermediate',1,60,'2026-09-22 08:00:00'),(8,'2024is015@stu.ucsc.cmb.ac.lk','Figma','Web Design','Expert',3,95,'2026-09-22 08:00:00'),(9,'2024is015@stu.ucsc.cmb.ac.lk','UI/UX Research','Web Design','Advanced',2,80,'2026-09-22 08:00:00'),(10,'2024is015@stu.ucsc.cmb.ac.lk','CSS / Sass','Frontend','Expert',3,95,'2026-09-22 08:00:00'),(11,'2024is032@stu.ucsc.cmb.ac.lk','Network Security','Cloud & DevOps','Intermediate',1,60,'2026-09-22 08:00:00'),(12,'2024is032@stu.ucsc.cmb.ac.lk','Linux System Administration','Cloud & DevOps','Advanced',2,80,'2026-09-22 08:00:00'),(13,'2024is001@stu.ucsc.cmb.ac.lk','Kubernetes','Cloud & DevOps','Intermediate',1,60,'2026-09-22 08:00:00'),(14,'2024is044@stu.ucsc.cmb.ac.lk','Java','Backend','Beginner',2,40,'2026-09-22 08:00:00'),(15,'2024is091@stu.ucsc.cmb.ac.lk','Flutter','Mobile Development','Advanced',2,80,'2026-09-25 10:20:10'),(16,'2024is091@stu.ucsc.cmb.ac.lk','Firebase','Cloud & DevOps','Advanced',2,75,'2026-09-25 10:20:10'),(17,'2024is091@stu.ucsc.cmb.ac.lk','Kotlin','Mobile Development','Intermediate',1,60,'2026-09-25 10:20:10'),(18,'2024is091@stu.ucsc.cmb.ac.lk','Figma','Web Design','Intermediate',1,55,'2026-09-25 10:20:10');
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
  `github` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `profile_completion` int(11) DEFAULT NULL,
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
INSERT INTO `student` VALUES ('2024is001@stu.ucsc.cmb.ac.lk','University OF Colombo','2','B.Sc. in Computer Science','Kavindu Perera',NULL,'Mobile App Developer and Machine Learning enthusiast with experience in Flutter and PyTorch.','https://github.com/kavinduperera','https://linkedin.com/in/kavindu-perera','https://kavindu.me',NULL),('2024is015@stu.ucsc.cmb.ac.lk','University OF Colombo','3','B.Sc. in Software Engineering','Nimasha Fernando',NULL,'UI/UX Designer and Frontend Specialist focused on intuitive user experiences and design systems.','https://github.com/nimasha-fernando','https://linkedin.com/in/nimashafernando','https://nimasha.design',NULL),('2024is032@stu.ucsc.cmb.ac.lk','University OF Colombo','1','B.Sc. in Information Systems','Sahan Wickramasinghe',NULL,'Cybersecurity student and backend explorer, working with Python, Linux, and network security.','https://github.com/sahanw','https://linkedin.com/in/sahan-wickrama','https://sahan.tech',NULL),('2024is044@stu.ucsc.cmb.ac.lk','University OF Colombo','3','B.Sc. in Computer Science','Dinithi Jayawardena',NULL,'Data Science & DevOps enthusiast. Passionate about Docker, Kubernetes, and predictive analytics.','https://github.com/dinithij','https://linkedin.com/in/dinithi-jayawardena','https://dinithi.io',NULL),('2024is058@stu.ucsc.cmb.ac.lk','University OF Colombo','2','B.Sc. in Information Systems','Haritha Induwara',NULL,'Aspiring Full-Stack Developer & Cloud Enthusiast passionate about building scalable web solutions.','https://github.com/harithainduwara','https://linkedin.com/in/harithainduwara','https://harithainduwara.dev',NULL),('2024is078@stu.ucsc.cmb.ac.lk','University OF Colombo','3','B.Sc. in Information Systems','Lasith Perera',NULL,'Machine Learning enthusiast. Passionate about Docker, Kubernetes, and predictive analytics.','https://github.com/lasith-perera','https://linkedin.com/in/lasith-perera','https://lasith.dev',NULL),('2024is091@stu.ucsc.cmb.ac.lk','University OF Colombo','3','B.Sc. in Software Engineering','Sachini Wijesinghe',NULL,'Mobile app developer who loves building clean, offline-first Flutter apps with Firebase. Interested in EdTech and accessibility.','https://github.com/sachiniw','https://linkedin.com/in/sachini-wijesinghe','https://sachini.dev',NULL),('harithainduwara05@gmail.com','SLIIT - Faculty of Computing','2024','B.Sc. in Information Systems','Haritha Induwara',NULL,NULL,NULL,NULL,NULL,0),('lasithperera2004@gmail.com','University of Colombo - School Of Computing','2024','B.Sc. in Information Systems','Haritha Induwara',NULL,NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_projects`
--

DROP TABLE IF EXISTS `student_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_projects` (
  `student_project_id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `project_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `progress` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`student_project_id`),
  KEY `project_id` (`project_id`),
  KEY `Email` (`Email`),
  CONSTRAINT `student_projects_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  CONSTRAINT `student_projects_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_projects`
--

LOCK TABLES `student_projects` WRITE;
/*!40000 ALTER TABLE `student_projects` DISABLE KEYS */;
INSERT INTO `student_projects` VALUES (1,'2024is058@stu.ucsc.cmb.ac.lk',1,'Full-Stack Developer',60,'In Progress'),(2,'2024is058@stu.ucsc.cmb.ac.lk',4,'Backend Lead',85,'In Progress'),(3,'2024is001@stu.ucsc.cmb.ac.lk',1,'ML Model Engineer',75,'In Progress'),(4,'2024is001@stu.ucsc.cmb.ac.lk',3,'Firmware Developer',40,'In Progress'),(5,'2024is015@stu.ucsc.cmb.ac.lk',2,'Lead UI Designer',90,'Completed'),(6,'2024is032@stu.ucsc.cmb.ac.lk',3,'Network & Security Lead',35,'In Progress'),(7,'2024is044@stu.ucsc.cmb.ac.lk',5,'Backend & DevOps Engineer',50,'In Progress'),(26,'2024is058@stu.ucsc.cmb.ac.lk',6,'NLP Engineer',10,'In Progress'),(27,'2024is001@stu.ucsc.cmb.ac.lk',6,'Backend Developer',10,'In Progress'),(28,'2024is015@stu.ucsc.cmb.ac.lk',6,'UI Designer',10,'In Progress'),(29,'2024is001@stu.ucsc.cmb.ac.lk',7,'Flutter Developer',15,'In Progress'),(30,'2024is078@stu.ucsc.cmb.ac.lk',7,'Firebase Engineer',15,'In Progress'),(31,'2024is015@stu.ucsc.cmb.ac.lk',8,'Frontend Developer',20,'In Progress'),(32,'2024is044@stu.ucsc.cmb.ac.lk',8,'UI Designer',20,'In Progress'),(33,'2024is058@stu.ucsc.cmb.ac.lk',9,'Backend Developer',100,'Completed'),(34,'2024is001@stu.ucsc.cmb.ac.lk',9,'Frontend Developer',100,'Completed'),(35,'2024is044@stu.ucsc.cmb.ac.lk',9,'DB Engineer',100,'Completed'),(38,'2024is058@stu.ucsc.cmb.ac.lk',12,'Mobile Developer',5,'In Progress');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `universityemails`
--

LOCK TABLES `universityemails` WRITE;
/*!40000 ALTER TABLE `universityemails` DISABLE KEYS */;
INSERT INTO `universityemails` VALUES ('University OF Colombo',1,'School Of Computing','stu.ucsc.cmb.ac.lk','Active','colombo 7'),('University of Moratuwa',2,'Faculty of Information Technology','itfac.mrt.ac.lk','Active','Katubedda, Moratuwa'),('University of Moratuwa',3,'Faculty of Engineering','eng.mrt.ac.lk','Active','Katubedda, Moratuwa'),('University of Kelaniya',4,'Faculty of Computing and Technology','fct.kln.ac.lk','Active','Kelaniya'),('University of Peradeniya',5,'Faculty of Engineering','eng.pdn.ac.lk','Active','Peradeniya, Kandy'),('University of Sri Jayewardenepura',6,'Faculty of Applied Sciences','fas.sjp.ac.lk','Active','Nugegoda'),('SLIIT',7,'Faculty of Computing','sliit.lk','Hold','Malabe');
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
INSERT INTO `user` VALUES ('2024is001@stu.ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','student','Active','','2026-08-20 04:30:00'),('2024is015@stu.ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','student','Active','','2026-08-20 05:00:00'),('2024is032@stu.ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','student','Active','','2026-08-21 03:20:00'),('2024is044@stu.ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','student','Active','','2026-08-21 04:10:00'),('2024is052@stu.ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','organization','Active','','2026-08-19 02:56:27'),('2024is058@stu.ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','student','Active','','2026-08-20 02:24:58'),('2024is078@stu.ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','student','Active','','2026-09-22 02:24:58'),('2024is091@stu.ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','student','Active','','2026-08-21 22:30:00'),('admin.sarath@skillbridge.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','admin','Active','','2026-08-19 04:00:00'),('careers@virtusa.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','company','Active','','2026-08-15 03:30:00'),('foss@sliit.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','organization','Active','','2026-08-25 07:45:00'),('harithainduwara05@gmail.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','student','Active','115531','2026-09-25 04:33:16'),('hr@company.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','company','Active','','2026-07-05 13:00:00'),('ieee@ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','organization','Active','','2026-08-22 06:15:00'),('lasithperera2004@gmail.com','b72f270c135d0601e51aaa6962a05bc15eea2d50','student','Active','','2026-09-26 16:34:27'),('recruitment@wso2.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','company','Active','','2026-08-16 05:00:00'),('rotaract@ucsc.cmb.ac.lk','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','organization','Active','','2026-08-24 09:20:00'),('skillbridge62@gmail.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','admin','Active','','2026-08-18 13:21:43'),('talent@ifs.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','company','Active','','2026-08-17 08:30:00');
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

-- Dump completed on 2026-09-27  1:36:55

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
