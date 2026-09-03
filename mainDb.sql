-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2026 at 08:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skillbridge_db`
--
CREATE DATABASE IF NOT EXISTS `skillbridge_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `skillbridge_db`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `profile_image` varchar(100) NOT NULL,
  `contactNumber` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`Name`, `Email`, `profile_image`, `contactNumber`) VALUES
('AdminHI', 'skillbridge62@gmail.com', 'admin_1787125323_6a855e4b16fe8.jpeg', '0771234560'),
('Sarath Kumara', 'admin.sarath@skillbridge.lk', 'admin_default.png', '0779876543');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `certificate_name` varchar(150) DEFAULT NULL,
  `issuer` varchar(150) DEFAULT NULL,
  `certificate_file` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificate_id`, `Email`, `certificate_name`, `issuer`, `certificate_file`, `status`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'AWS Certified Cloud Practitioner', 'Amazon Web Services', 'cert_aws_haritha.pdf', 'Approved'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 'Meta Front-End Developer Professional Certificate', 'Coursera - Meta', 'cert_meta_frontend.pdf', 'Approved'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 'Deep Learning Specialization', 'DeepLearning.AI', 'cert_deeplearning.pdf', 'Approved'),
(4, '2024is015@stu.ucsc.cmb.ac.lk', 'Google UX Design Professional Certificate', 'Coursera - Google', 'cert_google_ux.pdf', 'Approved'),
(5, '2024is032@stu.ucsc.cmb.ac.lk', 'CompTIA Security+ Certification', 'CompTIA', 'cert_security_plus.pdf', 'Pending'),
(6, '2024is044@stu.ucsc.cmb.ac.lk', 'Docker Certified Associate', 'Docker Inc.', 'cert_docker.pdf', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `Email` varchar(100) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `companytype` varchar(100) NOT NULL,
  `contactPersonName` varchar(100) NOT NULL,
  `contactNumber` varchar(17) NOT NULL,
  `website` varchar(200) NOT NULL,
  `location` varchar(100) NOT NULL,
  `Status` varchar(100) NOT NULL DEFAULT 'Unverified',
  `profile_img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`Email`, `Name`, `companytype`, `contactPersonName`, `contactNumber`, `website`, `location`, `Status`, `profile_img`) VALUES
('hr@company.com', 'HR Solutions Pvt Ltd', 'IT & Software Services', 'Nadeeka Munasingha', '0772343234', 'www.hrsolutions.lk', 'Colombo 07, Sri Lanka', 'Verify', 'company_1788438171_6a99669b20531.jpeg'),
('careers@virtusa.com', 'Virtusa Sri Lanka', 'IT Consulting & Services', 'Rohan Silva', '0112498000', 'www.virtusa.com', 'Orion City, Colombo 09', 'Verify', 'virtusa_logo.png'),
('recruitment@wso2.com', 'WSO2 Lanka (Pvt) Ltd', 'Software & Middleware', 'Ayesh Perera', '0112145345', 'www.wso2.com', 'Bauddhaloka Mawatha, Colombo 04', 'Verify', 'wso2_logo.png'),
('talent@ifs.com', 'IFS R&D International', 'Enterprise Software', 'Chamari Senanayake', '0112364400', 'www.ifs.com', 'Orion City, Colombo 09', 'Verify', 'ifs_logo.png');

-- --------------------------------------------------------

--
-- Table structure for table `complain`
--

DROP TABLE IF EXISTS `complain`;
CREATE TABLE `complain` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `discription` text NOT NULL,
  `priority` enum('LOW','MEDIUM','HIGH','URGENT') NOT NULL DEFAULT 'MEDIUM',
  `status` enum('PENDING','IN_REVIEW','DISMISSED','RESOLVED') NOT NULL DEFAULT 'PENDING',
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complain`
--

INSERT INTO `complain` (`id`, `email`, `title`, `discription`, `priority`, `status`, `create_at`, `update_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'Issue with certificate upload', 'Uploaded certificate PDF file size exceeds 5MB and produces an error without clear instructions.', 'MEDIUM', 'RESOLVED', '2026-08-22 04:45:00', '2026-08-23 08:30:00'),
(2, 'hr@company.com', 'Internship deadline display timezone bug', 'The internship post deadline date displays in UTC instead of Sri Lanka Standard Time (+05:30).', 'LOW', 'IN_REVIEW', '2026-08-25 04:00:00', '2026-08-25 05:50:00'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 'Profile image avatar caching', 'Updated profile picture did not immediately reflect on dashboard until browser cache was cleared.', 'LOW', 'PENDING', '2026-08-28 11:15:00', '2026-08-28 11:15:00'),
(4, 'careers@virtusa.com', 'Applicant notification delay', 'Instant notification email for incoming student applications was delayed by 30 minutes during peak hours.', 'HIGH', 'RESOLVED', '2026-08-29 05:40:00', '2026-08-30 03:00:00'),
(5, '2024is015@stu.ucsc.cmb.ac.lk', 'Team chat attachment limits', 'Students are unable to send design preview attachments larger than 2MB in student project chat.', 'MEDIUM', 'PENDING', '2026-09-01 08:50:00', '2026-09-01 08:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `full_name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Kavinda Jayasundara', 'kavinda.j@gmail.com', 'Partnership Inquiry for University Hackathon', 'Hello SkillBridge team, We would like to collaborate with SkillBridge for our upcoming annual university hackathon. Please get in touch with us.', '2026-08-21 03:42:00'),
(2, 'Ayesha Ranatunga', 'ayesha.r@outlook.com', 'Question about student verification process', 'Hi team, how long does it take for a newly registered student institutional email to be verified by campus admin?', '2026-08-24 10:10:00'),
(3, 'Supun Madushanka', 'supun.m@techcorp.lk', 'Employer Onboarding assistance needed', 'We are interested in listing multiple software engineering internship slots on your portal. Kindly send the employer handbook and terms.', '2026-08-27 12:35:00'),
(4, 'Chamika Dissanaike', 'chamika.d@gmail.com', 'Feedback on SkillBridge platform UI', 'The dark mode and responsive layout on the dashboard are fantastic! Great job on the user experience.', '2026-09-02 06:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

DROP TABLE IF EXISTS `internships`;
CREATE TABLE `internships` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `logo_text` varchar(10) DEFAULT NULL,
  `logo_style` varchar(100) DEFAULT NULL,
  `tech_tags` varchar(255) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `deadline` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`id`, `title`, `company`, `industry`, `logo_text`, `logo_style`, `tech_tags`, `duration`, `deadline`) VALUES
(1, 'UI/UX Designer Intern', 'HR Solutions Pvt Ltd', 'IT Services', 'TF', 'background: #e0e7ff; color: #4338ca;', 'Figma, Adobe XD, CSS', '6 Months', 'Nov 15, 2026'),
(2, 'Backend Developer Intern', 'HR Solutions Pvt Ltd', 'Software Development', 'GR', 'background: #dbeafe; color: #1e40af;', 'Node.js, PostgreSQL, Docker', '3 Months', 'Oct 20, 2026'),
(3, 'Associate Software Engineer Intern', 'Virtusa Sri Lanka', 'IT Consulting', 'VIR', 'background: #fee2e2; color: #b91c1c;', 'Java, Spring Boot, React, AWS', '6 Months', 'Dec 01, 2026'),
(4, 'Cloud Integration Intern', 'WSO2 Lanka (Pvt) Ltd', 'Software & Middleware', 'WSO2', 'background: #ffedd5; color: #c2410c;', 'Ballerina, Microservices, Kubernetes', '6 Months', 'Nov 30, 2026'),
(5, 'Full-Stack Developer Intern', 'IFS R&D International', 'Enterprise Software', 'IFS', 'background: #f3e8ff; color: #7e22ce;', 'Angular, C#, .NET Core, Oracle', '6 Months', 'Dec 15, 2026'),
(6, 'Mobile Application Developer Intern', 'HR Solutions Pvt Ltd', 'IT Solutions', 'HR', 'background: #dcfce7; color: #15803d;', 'Flutter, Dart, Firebase, REST APIs', '3 Months', 'Oct 31, 2026');

-- --------------------------------------------------------

--
-- Table structure for table `internship_applications`
--

DROP TABLE IF EXISTS `internship_applications`;
CREATE TABLE `internship_applications` (
  `application_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `applied_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internship_applications`
--

INSERT INTO `internship_applications` (`application_id`, `Email`, `internship_id`, `status`, `applied_date`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 1, 'Shortlisted', '2026-08-25'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 3, 'Under Review', '2026-08-28'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 2, 'Accepted', '2026-08-22'),
(4, '2024is001@stu.ucsc.cmb.ac.lk', 6, 'Shortlisted', '2026-08-26'),
(5, '2024is015@stu.ucsc.cmb.ac.lk', 1, 'Accepted', '2026-08-20'),
(6, '2024is032@stu.ucsc.cmb.ac.lk', 4, 'Pending', '2026-09-01'),
(7, '2024is044@stu.ucsc.cmb.ac.lk', 5, 'Under Review', '2026-08-30');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `Email`, `title`, `message`, `type`, `status`, `created_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'Application Shortlisted', 'Congratulations! Your application for UI/UX Designer Intern has been shortlisted.', 'application', 'Read', '2026-08-26 04:30:00'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 'New Project Invitation', 'You have been invited to collaborate on the project "Smart Campus Energy Monitor".', 'project', 'Unread', '2026-09-01 09:00:00'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 'Application Accepted', 'Global Retail has accepted your application for Backend Developer Intern!', 'application', 'Read', '2026-08-24 10:50:00'),
(4, 'hr@company.com', 'New Candidate Application', 'Haritha Induwara has applied for the Mobile Application Developer Intern position.', 'candidate', 'Unread', '2026-08-29 03:45:00'),
(5, 'careers@virtusa.com', 'Profile Verification Complete', 'Your company profile has been verified by SkillBridge Administration.', 'system', 'Read', '2026-08-21 05:30:00'),
(6, 'skillbridge62@gmail.com', 'New Organization Registration', 'Organization "IEEE Student Branch UCSC" has registered and is pending review.', 'admin', 'Unread', '2026-09-02 03:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `organization`
--

DROP TABLE IF EXISTS `organization`;
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
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`Name`, `orgtype`, `contactPersonName`, `contactNumber`, `website`, `location`, `Email`, `about`, `linkedin`, `twitter`, `facebook`, `logo`) VALUES
('MYDB Club', 'Student Society', 'Haritha Induwara', '0779063904', 'https://mydb.org', 'Colombo, Sri Lanka', '2024is052@stu.ucsc.cmb.ac.lk', 'Student database exploration and open source software initiatives.', 'https://linkedin.com/company/mydb', '', '', 'mydb_logo.png'),
('IEEE Student Branch UCSC', 'Academic Society', 'Kasun Rathnayake', '0715551234', 'https://ieee.ucsc.cmb.ac.lk', 'UCSC, Reid Avenue, Colombo 07', 'ieee@ucsc.cmb.ac.lk', 'Empowering students to innovate and excel in technology and engineering disciplines.', 'https://linkedin.com/company/ieee-ucsc', 'https://twitter.com/ieee_ucsc', 'https://facebook.com/ieeeucsc', 'ieee_logo.png'),
('Rotaract Club of UCSC', 'Community Service', 'Sachini Wickramasinghe', '0784449876', 'https://rotaractucsc.org', 'Colombo 07, Sri Lanka', 'rotaract@ucsc.cmb.ac.lk', 'Youth leadership and social community empowerment initiatives across Sri Lanka.', 'https://linkedin.com/company/rotaract-ucsc', '', 'https://facebook.com/rotaractucsc', 'rotaract_logo.png'),
('SLIIT FOSS Community', 'Open Source Community', 'Tharindu Gamage', '0761122334', 'https://foss.sliit.lk', 'New Kandy Road, Malabe', 'foss@sliit.lk', 'Promoting free and open-source software culture among university undergraduates.', 'https://linkedin.com/company/sliit-foss', 'https://twitter.com/sliitfoss', 'https://facebook.com/sliitfoss', 'foss_logo.png');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio`
--

DROP TABLE IF EXISTS `portfolio`;
CREATE TABLE `portfolio` (
  `portfolio_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `project_link` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolio`
--

INSERT INTO `portfolio` (`portfolio_id`, `Email`, `title`, `description`, `project_link`, `image`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'SkillBridge Platform', 'Modern role-based internship matching and student portfolio management system built with PHP and MySQL.', 'https://github.com/harithainduwara/Skill_Bridge', 'portfolio_skillbridge.png'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 'E-Commerce Microservices', 'High-performance microservices backend with product catalog, cart, and payment gateway integration.', 'https://github.com/harithainduwara/ecommerce-microservices', 'portfolio_ecommerce.png'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 'Skin Disease Detection with CNN', 'Deep learning mobile application that detects dermatological conditions from camera photos using Flutter and PyTorch.', 'https://github.com/kavinduperera/skin-disease-ai', 'portfolio_skindisease.png'),
(4, '2024is015@stu.ucsc.cmb.ac.lk', 'FinTech Digital Wallet UI/UX', 'Complete UX research, wireframing, design tokens, and interactive Figma prototyping for digital wallet application.', 'https://www.behance.net/gallery/fintech-app-redesign', 'portfolio_fintech.png'),
(5, '2024is032@stu.ucsc.cmb.ac.lk', 'Automated Network Security Scanner', 'Python CLI tool for security audits, port scanning, and CVE reporting using Nmap and Shodan APIs.', 'https://github.com/sahanw/vuln-scanner', 'portfolio_scanner.png'),
(6, '2024is044@stu.ucsc.cmb.ac.lk', 'Real-Time Kubernetes Analytics Dashboard', 'Kubernetes-deployed dashboard visualizing live streaming server metrics using React, Kafka, and Grafana.', 'https://github.com/dinithij/k8s-analytics-dashboard', 'portfolio_k8s.png');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
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
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `company`, `organization_email`, `icon`, `tag`, `tag_class`, `tech_stack`, `duration`, `members`, `deadline`, `category`, `keywords`, `description`, `learning_objectives`, `expected_outcomes`, `difficulty`, `preferred_year`, `visibility`, `status`, `posted_at`) VALUES
(1, 'AI Model Optimization', 'MYDB Club', '2024is052@stu.ucsc.cmb.ac.lk', '📊', 'High Demand', 'high-demand', 'Python, TensorFlow, AWS', '3 Months', 4, 'Oct 20, 2026', 'Artificial Intelligence', 'AI, Machine Learning, TensorFlow, Model Pruning', 'Optimizing large deep learning transformer models for edge devices and mobile browsers.', 'Learn quantization, model pruning, ONNX runtime optimization.', 'Production ready lightweight inference model with <50ms latency.', 'Intermediate', 'Any Year', 'Public', 'open', '2026-08-17 13:46:49'),
(2, 'Cloud Migration UI/UX', 'MYDB Club', '2024is052@stu.ucsc.cmb.ac.lk', '🎨', 'UI/UX', 'ui-ux', 'React, Figma, Tailwind', '2 Months', 2, 'Nov 15, 2026', 'UI/UX Design', 'Design Systems, User Research, Prototyping', 'Modernizing legacy university student portal interfaces into accessible, responsive cloud UI.', 'Master accessible design principles (WCAG 2.1) and component library architecture.', 'Comprehensive Figma design system and React component kit.', 'Intermediate', 'Any Year', 'Public', 'open', '2026-08-17 13:46:49'),
(3, 'Smart Campus Energy Monitor', 'IEEE Student Branch UCSC', 'ieee@ucsc.cmb.ac.lk', '⚡', 'IoT & Hardware', 'iot-hardware', 'ESP32, MQTT, Node.js, Grafana', '4 Months', 5, 'Dec 10, 2026', 'Internet of Things', 'IoT, Smart Campus, Green Tech, Sensors', 'Building IoT power meters across faculty computer labs to track and minimize electricity waste in real-time.', 'Gain hands-on experience with microcontroller programming, MQTT broker protocols, and sensor calibration.', 'Operational live campus energy dashboard and automated alert system for abnormal power surges.', 'Advanced', '3rd Year', 'Public', 'open', '2026-08-25 08:30:00'),
(4, 'Open Source Blood Donation Portal', 'Rotaract Club of UCSC', 'rotaract@ucsc.cmb.ac.lk', '❤️', 'Social Good', 'community-impact', 'PHP, MySQL, Bootstrap, Twilio', '2 Months', 3, 'Nov 01, 2026', 'Web Development', 'Community, Healthcare, SMS Gateway, Emergency Alerts', 'A streamlined digital platform connecting volunteer blood donors with regional hospitals during critical shortages.', 'Build secure role-based portals, integrate SMS alerts, and implement geolocation search.', 'Fully tested web application deployed for nationwide donor registration drives.', 'Beginner', '1st Year', 'Public', 'open', '2026-08-28 05:00:00'),
(5, 'FOSS Contributor Leaderboard', 'SLIIT FOSS Community', 'foss@sliit.lk', '🚀', 'Open Source', 'open-source', 'Go, GitHub GraphQL API, Next.js', '3 Months', 4, 'Nov 25, 2026', 'DevOps & Tooling', 'GitHub API, Go, Open Source, Hacktoberfest', 'Gamified leaderboard tracking pull requests, commits, and reviews of university students contributing to open source repositories.', 'Work with GraphQL APIs, asynchronous queue processing in Go, and modern frontend dashboards.', 'Automated ranking system used for annual Hacktoberfest and code sprint awards.', 'Intermediate', '2nd Year', 'Public', 'open', '2026-09-01 10:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
CREATE TABLE `skills` (
  `skill_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `percentage` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skill_id`, `Email`, `skill_name`, `category`, `level`, `experience`, `percentage`, `status`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'React', 'Frontend', 'Advanced', '2 years', 85, 'Verified'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 'PHP', 'Backend', 'Expert', '3 years', 90, 'Verified'),
(3, '2024is058@stu.ucsc.cmb.ac.lk', 'MySQL', 'Database', 'Advanced', '2 years', 88, 'Verified'),
(4, '2024is058@stu.ucsc.cmb.ac.lk', 'Docker', 'DevOps', 'Intermediate', '1 year', 70, 'Pending'),
(5, '2024is001@stu.ucsc.cmb.ac.lk', 'Python', 'Programming', 'Expert', '3 years', 92, 'Verified'),
(6, '2024is001@stu.ucsc.cmb.ac.lk', 'Flutter', 'Mobile', 'Advanced', '2 years', 80, 'Verified'),
(7, '2024is001@stu.ucsc.cmb.ac.lk', 'PyTorch', 'Machine Learning', 'Intermediate', '1.5 years', 75, 'Pending'),
(8, '2024is015@stu.ucsc.cmb.ac.lk', 'Figma', 'Design', 'Expert', '3 years', 95, 'Verified'),
(9, '2024is015@stu.ucsc.cmb.ac.lk', 'UI/UX Research', 'Design', 'Advanced', '2 years', 85, 'Verified'),
(10, '2024is015@stu.ucsc.cmb.ac.lk', 'CSS / Sass', 'Frontend', 'Expert', '3 years', 90, 'Verified'),
(11, '2024is032@stu.ucsc.cmb.ac.lk', 'Network Security', 'Security', 'Intermediate', '1 year', 72, 'Pending'),
(12, '2024is032@stu.ucsc.cmb.ac.lk', 'Linux System Administration', 'DevOps', 'Advanced', '2 years', 82, 'Verified'),
(13, '2024is044@stu.ucsc.cmb.ac.lk', 'Kubernetes', 'Cloud & DevOps', 'Intermediate', '1 year', 68, 'Pending'),
(14, '2024is044@stu.ucsc.cmb.ac.lk', 'Java', 'Backend', 'Advanced', '2 years', 84, 'Verified');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
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
  `profile_completion` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`Email`, `University`, `year`, `degree`, `Name`, `profile_image`, `bio`, `github`, `linkedin`, `website`, `profile_completion`) VALUES
('2024is058@stu.ucsc.cmb.ac.lk', 'University OF Colombo', 'Year 2', 'B.Sc. in Information Systems', 'Haritha Induwara', 'profile_1786995576_6a836378cc4ba.png', 'Aspiring Full-Stack Developer & Cloud Enthusiast passionate about building scalable web solutions.', 'https://github.com/harithainduwara', 'https://linkedin.com/in/harithainduwara', 'https://harithainduwara.dev', 95),
('2024is001@stu.ucsc.cmb.ac.lk', 'University OF Colombo', 'Year 2', 'B.Sc. in Computer Science', 'Kavindu Perera', NULL, 'Mobile App Developer and Machine Learning enthusiast with experience in Flutter and PyTorch.', 'https://github.com/kavinduperera', 'https://linkedin.com/in/kavindu-perera', 'https://kavindu.me', 85),
('2024is015@stu.ucsc.cmb.ac.lk', 'University OF Colombo', 'Year 3', 'B.Sc. in Software Engineering', 'Nimasha Fernando', NULL, 'UI/UX Designer and Frontend Specialist focused on intuitive user experiences and design systems.', 'https://github.com/nimasha-fernando', 'https://linkedin.com/in/nimashafernando', 'https://nimasha.design', 90),
('2024is032@stu.ucsc.cmb.ac.lk', 'University OF Colombo', 'Year 1', 'B.Sc. in Information Systems', 'Sahan Wickramasinghe', NULL, 'Cybersecurity student and backend explorer, working with Python, Linux, and network security.', 'https://github.com/sahanw', 'https://linkedin.com/in/sahan-wickrama', 'https://sahan.tech', 75),
('2024is044@stu.ucsc.cmb.ac.lk', 'University OF Colombo', 'Year 3', 'B.Sc. in Computer Science', 'Dinithi Jayawardena', NULL, 'Data Science & DevOps enthusiast. Passionate about Docker, Kubernetes, and predictive analytics.', 'https://github.com/dinithij', 'https://linkedin.com/in/dinithi-jayawardena', 'https://dinithi.io', 80);

-- --------------------------------------------------------

--
-- Table structure for table `student_projects`
--

DROP TABLE IF EXISTS `student_projects`;
CREATE TABLE `student_projects` (
  `student_project_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `project_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `progress` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_projects`
--

INSERT INTO `student_projects` (`student_project_id`, `Email`, `project_id`, `role`, `progress`, `status`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 1, 'Full-Stack Developer', 60, 'In Progress'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 4, 'Backend Lead', 85, 'In Progress'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 1, 'ML Model Engineer', 75, 'In Progress'),
(4, '2024is001@stu.ucsc.cmb.ac.lk', 3, 'Firmware Developer', 40, 'In Progress'),
(5, '2024is015@stu.ucsc.cmb.ac.lk', 2, 'Lead UI Designer', 90, 'Completed'),
(6, '2024is032@stu.ucsc.cmb.ac.lk', 3, 'Network & Security Lead', 35, 'In Progress'),
(7, '2024is044@stu.ucsc.cmb.ac.lk', 5, 'Backend & DevOps Engineer', 50, 'In Progress');

-- --------------------------------------------------------

--
-- Table structure for table `universityemails`
--

DROP TABLE IF EXISTS `universityemails`;
CREATE TABLE `universityemails` (
  `University` varchar(100) NOT NULL,
  `no` int(11) NOT NULL,
  `faculty` varchar(100) NOT NULL,
  `emailEx` varchar(100) NOT NULL,
  `Status` varchar(10) NOT NULL DEFAULT 'De-Active',
  `Location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `universityemails`
--

INSERT INTO `universityemails` (`University`, `no`, `faculty`, `emailEx`, `Status`, `Location`) VALUES
('University OF Colombo', 1, 'School Of Computing', 'stu.ucsc.cmb.ac.lk', 'Active', 'colombo 7'),
('University of Moratuwa', 2, 'Faculty of Information Technology', 'itfac.mrt.ac.lk', 'Active', 'Katubedda, Moratuwa'),
('University of Moratuwa', 3, 'Faculty of Engineering', 'eng.mrt.ac.lk', 'Active', 'Katubedda, Moratuwa'),
('University of Kelaniya', 4, 'Faculty of Computing and Technology', 'fct.kln.ac.lk', 'Active', 'Kelaniya'),
('University of Peradeniya', 5, 'Faculty of Engineering', 'eng.pdn.ac.lk', 'Active', 'Peradeniya, Kandy'),
('University of Sri Jayewardenepura', 6, 'Faculty of Applied Sciences', 'fas.sjp.ac.lk', 'Active', 'Nugegoda'),
('SLIIT', 7, 'Faculty of Computing', 'sliit.lk', 'Active', 'Malabe');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `Email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'De-Active',
  `verification_code` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`Email`, `password`, `role`, `status`, `verification_code`, `created_at`) VALUES
('2024is001@stu.ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'student', 'Active', '', '2026-08-20 04:30:00'),
('2024is015@stu.ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'student', 'Active', '', '2026-08-20 05:00:00'),
('2024is032@stu.ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'student', 'Active', '', '2026-08-21 03:20:00'),
('2024is044@stu.ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'student', 'Active', '', '2026-08-21 04:10:00'),
('2024is052@stu.ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'organization', 'Active', '', '2026-08-19 02:56:27'),
('2024is058@stu.ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'student', 'Active', '', '2026-08-20 02:24:58'),
('admin.sarath@skillbridge.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'admin', 'Active', '', '2026-08-19 04:00:00'),
('careers@virtusa.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'company', 'Active', '', '2026-08-15 03:30:00'),
('foss@sliit.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'organization', 'Active', '', '2026-08-25 07:45:00'),
('hr@company.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'company', 'Active', '', '2026-07-05 13:00:00'),
('ieee@ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'organization', 'Active', '', '2026-08-22 06:15:00'),
('recruitment@wso2.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'company', 'Active', '', '2026-08-16 05:00:00'),
('rotaract@ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'organization', 'Active', '', '2026-08-24 09:20:00'),
('skillbridge62@gmail.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'admin', 'Active', '', '2026-08-18 13:21:43'),
('talent@ifs.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'company', 'Active', '', '2026-08-17 08:30:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Email`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`Email`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `complain`
--
ALTER TABLE `complain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `internship_applications`
--
ALTER TABLE `internship_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `Email` (`Email`),
  ADD KEY `internship_id` (`internship_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`Email`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`portfolio_id`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_projects_org` (`organization_email`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`Email`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `student_projects`
--
ALTER TABLE `student_projects`
  ADD PRIMARY KEY (`student_project_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `universityemails`
--
ALTER TABLE `universityemails`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `complain`
--
ALTER TABLE `complain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `internship_applications`
--
ALTER TABLE `internship_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `portfolio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `student_projects`
--
ALTER TABLE `student_projects`
  MODIFY `student_project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `universityemails`
--
ALTER TABLE `universityemails`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `company`
--
ALTER TABLE `company`
  ADD CONSTRAINT `company_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`);

--
-- Constraints for table `complain`
--
ALTER TABLE `complain`
  ADD CONSTRAINT `complain_ibfk_1` FOREIGN KEY (`email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `internship_applications`
--
ALTER TABLE `internship_applications`
  ADD CONSTRAINT `internship_applications_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `internship_applications_ibfk_2` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `organization`
--
ALTER TABLE `organization`
  ADD CONSTRAINT `organization_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`);

--
-- Constraints for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD CONSTRAINT `portfolio_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_org` FOREIGN KEY (`organization_email`) REFERENCES `organization` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_projects`
--
ALTER TABLE `student_projects`
  ADD CONSTRAINT `student_projects_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_projects_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
