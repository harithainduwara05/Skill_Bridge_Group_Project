-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 07:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
('Sarath Kumara', 'admin.sarath@skillbridge.lk', 'admin_default.png', '0779876543'),
('AdminHI', 'skillbridge62@gmail.com', 'admin_1787125323_6a855e4b16fe8.jpeg', '0771234560');

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
  `issue_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `verified_by` varchar(100) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificate_id`, `Email`, `certificate_name`, `issuer`, `certificate_file`, `issue_date`, `status`, `verified_by`, `verified_at`, `created_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'AWS Certified Cloud Practitioner', 'Amazon Web Services', 'cert_aws_haritha.pdf', '2026-04-10', 'Approved', 'skillbridge62@gmail.com', '2026-08-20 10:00:00', '2026-08-20 09:30:00'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 'Meta Front-End Developer Professional Certificate', 'Coursera - Meta', 'cert_meta_frontend.pdf', '2026-06-15', 'Approved', 'skillbridge62@gmail.com', '2026-08-20 10:15:00', '2026-08-20 09:30:00'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 'Deep Learning Specialization', 'DeepLearning.AI', 'cert_deeplearning.pdf', '2026-05-18', 'Approved', 'skillbridge62@gmail.com', '2026-08-21 11:00:00', '2026-08-21 09:00:00'),
(4, '2024is015@stu.ucsc.cmb.ac.lk', 'Google UX Design Professional Certificate', 'Coursera - Google', 'cert_google_ux.pdf', '2026-05-20', 'Approved', 'skillbridge62@gmail.com', '2026-08-21 11:30:00', '2026-08-21 09:30:00'),
(5, '2024is032@stu.ucsc.cmb.ac.lk', 'CompTIA Security+ Certification', 'CompTIA', 'cert_security_plus.pdf', '2026-07-01', 'Pending', NULL, NULL, '2026-08-22 08:00:00'),
(6, '2024is044@stu.ucsc.cmb.ac.lk', 'Docker Certified Associate', 'Docker Inc.', 'cert_docker.pdf', '2026-06-25', 'Approved', 'skillbridge62@gmail.com', '2026-08-22 14:00:00', '2026-08-22 10:00:00'),
(7, '2024is091@stu.ucsc.cmb.ac.lk', 'Associate Android Developer', 'Google', 'cert_android_sachini.pdf', '2026-07-10', 'Approved', 'skillbridge62@gmail.com', '2026-08-23 09:00:00', '2026-08-23 08:30:00'),
(8, '2024is091@stu.ucsc.cmb.ac.lk', 'Flutter & Dart - The Complete Guide', 'Udemy', 'cert_flutter_sachini.pdf', '2026-07-28', 'Approved', 'skillbridge62@gmail.com', '2026-08-23 09:15:00', '2026-08-23 08:30:00');

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
('careers@virtusa.com', 'Virtusa Sri Lanka', 'IT Consulting & Services', 'Rohan Silva', '0112498000', 'www.virtusa.com', 'Orion City, Colombo 09', 'Verify', 'virtusa_logo.png'),
('hr@company.com', 'HR Solutions Pvt Ltd', 'IT & Software Services', 'Nadeeka Munasingha', '0772343234', 'www.hrsolutions.lk', 'Colombo 07, Sri Lanka', 'Verify', 'company_1788438171_6a99669b20531.jpeg'),
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
  `category` varchar(50) DEFAULT 'Technical',
  `discription` text NOT NULL,
  `priority` enum('LOW','MEDIUM','HIGH','URGENT') NOT NULL DEFAULT 'MEDIUM',
  `status` enum('PENDING','IN_REVIEW','DISMISSED','RESOLVED') NOT NULL DEFAULT 'PENDING',
  `resolution_notes` text DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complain`
--

INSERT INTO `complain` (`id`, `email`, `title`, `category`, `discription`, `priority`, `status`, `resolution_notes`, `create_at`, `update_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'Issue with certificate upload', 'Technical', 'Uploaded certificate PDF file size exceeds 5MB and produces an error without clear instructions.', 'MEDIUM', 'DISMISSED', 'Resolved by Administrator via Quick Action.', '2026-08-22 04:45:00', '2026-09-26 18:45:43'),
(2, 'hr@company.com', 'Internship deadline display timezone bug', 'Technical', 'The internship post deadline date displays in UTC instead of Sri Lanka Standard Time (+05:30).', 'HIGH', 'DISMISSED', 'Resolved by Administrator via Quick Action.', '2026-08-25 04:00:00', '2026-09-26 18:45:34'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 'Profile image avatar caching', 'Technical', 'Updated profile picture did not immediately reflect on dashboard until browser cache was cleared.', 'HIGH', 'RESOLVED', 'Resolved by Administrator via Quick Action.', '2026-08-28 11:15:00', '2026-09-26 18:46:35'),
(4, 'careers@virtusa.com', 'Applicant notification delay', 'Organization', 'Instant notification email for incoming student applications was delayed by 30 minutes during peak hours.', 'HIGH', 'IN_REVIEW', 'Resolved by Administrator via Quick Action.', '2026-08-29 05:40:00', '2026-09-26 19:29:26'),
(5, '2024is015@stu.ucsc.cmb.ac.lk', 'Team chat attachment limits', 'Technical', 'Students are unable to send design preview attachments larger than 2MB in student project chat.', 'MEDIUM', 'DISMISSED', 'Resolved by Administrator via Quick Action.', '2026-09-01 08:50:00', '2026-09-26 18:45:36'),
(6, '2024is032@stu.ucsc.cmb.ac.lk', 'Unable to upload internship application document', 'Technical', 'When attempting to submit a CV for the Cloud Engineering internship, the upload component fails after 30 seconds with network timeout.', 'HIGH', 'RESOLVED', 'Dismissed by Administrator as non-actionable.', '2026-09-18 04:50:00', '2026-09-26 18:46:14'),
(7, 'foss@sliit.lk', 'Inaccurate project duration specified by mentor', 'Organization', 'The project duration listed on the platform was 3 months, but partner company modified milestone schedule to 6 months without prior discussion.', 'MEDIUM', 'DISMISSED', 'Resolved by Administrator via Quick Action.', '2026-09-19 08:45:00', '2026-09-26 16:12:39'),
(8, '2024is044@stu.ucsc.cmb.ac.lk', 'Discrepancy in student evaluation score', 'Academic', 'Academic evaluation report submitted by external supervisor shows missing rubric breakdown for sprint 3.', 'LOW', 'DISMISSED', NULL, '2026-09-15 02:30:00', '2026-09-26 18:45:40'),
(9, '2024is078@stu.ucsc.cmb.ac.lk', 'Password reset verification email not received', 'Technical', 'Password reset link sent to official university student inbox does not arrive within the 15-minute token expiry window.', 'HIGH', 'IN_REVIEW', 'Resolved by Administrator via Quick Action.', '2026-09-22 06:00:00', '2026-09-26 18:46:08'),
(10, 'recruitment@wso2.com', 'Company profile picture upload failing', 'Technical', 'Company brand logo in PNG format fails with validation error even though dimensions are 400x400.', 'LOW', 'DISMISSED', 'Resolved by Administrator via Quick Action.', '2026-09-23 04:10:00', '2026-09-26 18:45:46'),
(11, 'talent@ifs.com', 'Applicant absent for scheduled technical interview', 'User Conduct', 'Applicant confirmed slot for interview on Friday 3:00 PM but failed to join the video session without notice.', 'MEDIUM', 'DISMISSED', 'Resolved by Administrator via Quick Action.', '2026-09-24 10:30:00', '2026-09-25 16:27:09'),
(12, 'harithainduwara05@gmail.com', 'Project milestone review overdue by supervisor', 'Project & Milestones', 'Final deliverable for AI Research Project was submitted 14 days ago and supervisor review is pending past SLA deadline.', 'HIGH', 'DISMISSED', 'Duplicate submission resolved in ticket CMP-0005', '2026-09-25 03:00:00', '2026-09-26 19:22:29');

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
(4, 'Chamika Dissanaike', 'chamika.d@gmail.com', 'Feedback on SkillBridge platform UI', 'The dark mode and responsive layout on the dashboard are fantastic! Great job on the user experience.', '2026-09-02 06:00:00'),
(5, 'Haritha Induwara', 'harithainduwara05@gmail.com', 'Registration Problem', 'sdfsjdfsgd', '2026-09-25 05:44:37');

-- --------------------------------------------------------

--
-- Table structure for table `degrees`
--

DROP TABLE IF EXISTS `degrees`;
CREATE TABLE `degrees` (
  `degree_id` int(11) NOT NULL,
  `domain` varchar(100) NOT NULL,
  `degree_name` varchar(150) NOT NULL,
  `total_years` tinyint(4) NOT NULL DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `degrees`
--

INSERT INTO `degrees` (`degree_id`, `domain`, `degree_name`, `total_years`) VALUES
(1, 'stu.ucsc.cmb.ac.lk', 'B.Sc. in Computer Science', 3),
(2, 'stu.ucsc.cmb.ac.lk', 'B.Sc. in Software Engineering', 4),
(3, 'stu.ucsc.cmb.ac.lk', 'B.Sc. in Information Systems', 3),
(4, 'sliit.lk', 'B.Sc. in Information Systems', 4),
(5, 'itfac.mrt.ac.lk', 'B.Sc. in Information Technology', 4);

-- --------------------------------------------------------

--
-- Table structure for table `feedback_improvements`
--

DROP TABLE IF EXISTS `feedback_improvements`;
CREATE TABLE `feedback_improvements` (
  `feedback_id` int(11) NOT NULL,
  `area` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

DROP TABLE IF EXISTS `internships`;
CREATE TABLE `internships` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `company_email` varchar(100) NOT NULL,
  `industry_id` smallint(5) UNSIGNED DEFAULT NULL,
  `logo_text` varchar(10) DEFAULT NULL,
  `logo_style` varchar(100) DEFAULT NULL,
  `duration_value` smallint(5) UNSIGNED DEFAULT NULL,
  `duration_unit` enum('day','week','month','year') DEFAULT 'month',
  `deadline_date` date DEFAULT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`id`, `title`, `company_email`, `industry_id`, `logo_text`, `logo_style`, `duration_value`, `duration_unit`, `deadline_date`, `description`, `cover_image`, `academic_year`, `experience_level`, `vacancies`, `internship_type`, `work_mode`, `location`, `start_date`, `paid_status`, `stipend`, `responsibilities`, `benefits`, `supporting_document`, `created_at`) VALUES
(1, 'UI/UX Designer Intern', 'hr@company.com', 1, 'TF', 'background: #e0e7ff; color: #4338ca;', 6, 'month', '2026-11-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-20 04:00:00'),
(2, 'Backend Developer Intern', 'hr@company.com', 2, 'GR', 'background: #dbeafe; color: #1e40af;', 3, 'month', '2026-10-20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-20 04:00:00'),
(3, 'Associate Software Engineer Intern', 'careers@virtusa.com', 3, 'VIR', 'background: #fee2e2; color: #b91c1c;', 6, 'month', '2026-12-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-20 04:00:00'),
(4, 'Cloud Integration Intern', 'recruitment@wso2.com', 4, 'WSO2', 'background: #ffedd5; color: #c2410c;', 6, 'month', '2026-11-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-20 04:00:00'),
(5, 'Full-Stack Developer Intern', 'talent@ifs.com', 5, 'IFS', 'background: #f3e8ff; color: #7e22ce;', 6, 'month', '2026-12-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-20 04:00:00'),
(6, 'Mobile Application Developer Intern', 'hr@company.com', 6, 'HR', 'background: #dcfce7; color: #15803d;', 3, 'month', '2026-10-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-20 04:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `internship_applications`
--

DROP TABLE IF EXISTS `internship_applications`;
CREATE TABLE `internship_applications` (
  `application_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `application_title` varchar(255) DEFAULT NULL,
  `cover_note` text DEFAULT NULL,
  `relevant_experience` text DEFAULT NULL,
  `selection_rationale` text DEFAULT NULL,
  `resume_file` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `applied_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internship_applications`
--

INSERT INTO `internship_applications` (`application_id`, `Email`, `internship_id`, `application_title`, `cover_note`, `relevant_experience`, `selection_rationale`, `resume_file`, `status`, `applied_date`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 1, NULL, NULL, NULL, NULL, NULL, 'Shortlisted', '2026-08-25'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 3, NULL, NULL, NULL, NULL, NULL, 'Under Review', '2026-08-28'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 2, NULL, NULL, NULL, NULL, NULL, 'Accepted', '2026-08-22'),
(4, '2024is001@stu.ucsc.cmb.ac.lk', 6, NULL, NULL, NULL, NULL, NULL, 'Shortlisted', '2026-08-26'),
(5, '2024is015@stu.ucsc.cmb.ac.lk', 1, NULL, NULL, NULL, NULL, NULL, 'Accepted', '2026-08-20'),
(6, '2024is032@stu.ucsc.cmb.ac.lk', 4, NULL, NULL, NULL, NULL, NULL, 'Pending', '2026-09-01'),
(7, '2024is044@stu.ucsc.cmb.ac.lk', 5, NULL, NULL, NULL, NULL, NULL, 'Under Review', '2026-08-30');

-- --------------------------------------------------------

--
-- Table structure for table `internship_industries`
--

DROP TABLE IF EXISTS `internship_industries`;
CREATE TABLE `internship_industries` (
  `industry_id` smallint(5) UNSIGNED NOT NULL,
  `industry_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internship_industries`
--

INSERT INTO `internship_industries` (`industry_id`, `industry_name`) VALUES
(5, 'Enterprise Software'),
(3, 'IT Consulting'),
(1, 'IT Services'),
(6, 'IT Solutions'),
(4, 'Software & Middleware'),
(2, 'Software Development');

-- --------------------------------------------------------

--
-- Table structure for table `internship_interviews`
--

DROP TABLE IF EXISTS `internship_interviews`;
CREATE TABLE `internship_interviews` (
  `interview_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `interview_date` date DEFAULT NULL,
  `interview_time` varchar(50) DEFAULT NULL,
  `interview_type` varchar(100) DEFAULT NULL,
  `interview_link` varchar(255) DEFAULT NULL,
  `interview_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internship_interviews`
--

INSERT INTO `internship_interviews` (`interview_id`, `application_id`, `interview_date`, `interview_time`, `interview_type`, `interview_link`, `interview_notes`, `created_at`) VALUES
(1, 3, '2026-10-24', '10:30 AM - 11:30 AM', 'Technical Interview (Google Meet)', 'https://meet.google.com/xyz-abcd-qrs', 'Focus on Node.js, PostgreSQL, and Docker containerization.', '2026-08-23 09:00:00'),
(2, 5, '2026-10-25', '02:00 PM - 02:45 PM', 'Video Interview (Google Meet / Zoom)', 'https://meet.google.com/uvw-efgh-ijk', 'Portfolio walkthrough: Figma design tokens and design systems.', '2026-08-21 11:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `internship_moderation_logs`
--

DROP TABLE IF EXISTS `internship_moderation_logs`;
CREATE TABLE `internship_moderation_logs` (
  `id` int(11) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `admin_reason` text NOT NULL,
  `reactivation_status` varchar(50) DEFAULT NULL,
  `status` enum('Suspended','Active','Terminated') NOT NULL DEFAULT 'Suspended',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `internship_tech_tags`
--

DROP TABLE IF EXISTS `internship_tech_tags`;
CREATE TABLE `internship_tech_tags` (
  `tag_id` int(10) UNSIGNED NOT NULL,
  `tag_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internship_tech_tags`
--

INSERT INTO `internship_tech_tags` (`tag_id`, `tag_name`) VALUES
(16, '.NET Core'),
(2, 'Adobe XD'),
(14, 'Angular'),
(10, 'AWS'),
(11, 'Ballerina'),
(15, 'C#'),
(3, 'CSS'),
(19, 'Dart'),
(6, 'Docker'),
(1, 'Figma'),
(20, 'Firebase'),
(18, 'Flutter'),
(7, 'Java'),
(13, 'Kubernetes'),
(12, 'Microservices'),
(4, 'Node.js'),
(17, 'Oracle'),
(5, 'PostgreSQL'),
(9, 'React'),
(21, 'REST APIs'),
(8, 'Spring Boot');

-- --------------------------------------------------------

--
-- Table structure for table `internship_tech_tag_map`
--

DROP TABLE IF EXISTS `internship_tech_tag_map`;
CREATE TABLE `internship_tech_tag_map` (
  `internship_id` int(11) NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internship_tech_tag_map`
--

INSERT INTO `internship_tech_tag_map` (`internship_id`, `tag_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(4, 11),
(4, 12),
(4, 13),
(5, 14),
(5, 15),
(5, 16),
(5, 17),
(6, 18),
(6, 19),
(6, 20),
(6, 21);

-- --------------------------------------------------------

--
-- Table structure for table `master_skills`
--

DROP TABLE IF EXISTS `master_skills`;
CREATE TABLE `master_skills` (
  `skill_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `skill_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_skills`
--

INSERT INTO `master_skills` (`skill_id`, `category_id`, `skill_name`) VALUES
(1, 1, 'React'),
(2, 2, 'PHP'),
(3, 3, 'MySQL'),
(4, 4, 'Docker'),
(5, 5, 'Python'),
(6, 6, 'Flutter'),
(7, 7, 'PyTorch'),
(8, 8, 'Figma'),
(9, 8, 'UI/UX Research'),
(10, 1, 'CSS / Sass'),
(11, 4, 'Network Security'),
(12, 4, 'Linux System Administration'),
(13, 4, 'Kubernetes'),
(14, 2, 'Java'),
(15, 4, 'Firebase'),
(16, 6, 'Kotlin'),
(17, 1, 'JavaScript'),
(18, 1, 'Tailwind CSS'),
(19, 2, 'Node.js');

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
(1, '2024is078@stu.ucsc.cmb.ac.lk', 'Application Shortlisted', 'Congratulations! Your application for UI/UX Designer Intern has been shortlisted.', 'application', 'Read', '2026-08-26 04:30:00'),
(2, '2024is078@stu.ucsc.cmb.ac.lk', 'New Project Invitation', 'You have been invited to collaborate on the project "Smart Campus Energy Monitor".', 'project', 'Unread', '2026-09-01 09:00:00'),
(3, '2024is078@stu.ucsc.cmb.ac.lk', 'Application Accepted', 'Global Retail has accepted your application for Backend Developer Intern!', 'application', 'Read', '2026-08-24 10:50:00'),
(4, 'hr@company.com', 'New Candidate Application', 'Haritha Induwara has applied for the Mobile Application Developer Intern position.', 'candidate', 'Unread', '2026-08-29 03:45:00'),
(5, 'careers@virtusa.com', 'Profile Verification Complete', 'Your company profile has been verified by SkillBridge Administration.', 'system', 'Read', '2026-08-21 05:30:00'),
(6, 'skillbridge62@gmail.com', 'New Organization Registration', 'Organization \"IEEE Student Branch UCSC\" has registered and is pending review.', 'admin', 'Unread', '2026-09-02 03:15:00'),
(7, 'foss@sliit.lk', 'New Project Proposal Received', 'A new student proposal has been submitted for your API Integration project.', 'proposal', 'Unread', '2026-09-24 20:31:41'),
(8, 'foss@sliit.lk', 'New Application Received', 'A student has applied for one of your organization projects.', 'candidate', 'Unread', '2026-09-24 20:31:41'),
(9, 'foss@sliit.lk', 'New Team Member Joined', 'A new student has joined the Project Phoenix team.', 'team', 'Read', '2026-09-24 20:31:41'),
(10, 'foss@sliit.lk', 'Project Progress Updated', 'The Smart Campus Energy Monitor team has submitted a new progress update.', 'project', 'Read', '2026-09-24 20:31:41'),
(20, 'foss@sliit.lk', 'Project Put On Hold', 'Your project \"Smart Clinic Queue & Appointment System\" was put on hold by the Admin because it breaks 4 project posting rules. Open Manage Projects to see the reason and fix it.', 'project', 'Unread', '2026-09-24 09:29:29');

-- --------------------------------------------------------

--
-- Table structure for table `organization`
--

DROP TABLE IF EXISTS `organization`;
CREATE TABLE `organization` (
  `Email` varchar(100) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `orgtype` varchar(100) NOT NULL,
  `contactPersonName` varchar(100) NOT NULL,
  `contactNumber` varchar(17) NOT NULL,
  `website` varchar(200) NOT NULL,
  `location` varchar(100) NOT NULL,
  `about` text DEFAULT NULL,
  `linkedin` varchar(200) DEFAULT NULL,
  `twitter` varchar(200) DEFAULT NULL,
  `facebook` varchar(200) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`Email`, `Name`, `orgtype`, `contactPersonName`, `contactNumber`, `website`, `location`, `about`, `linkedin`, `twitter`, `facebook`, `logo`) VALUES
('2024is052@stu.ucsc.cmb.ac.lk', 'MYDB Club', 'Student Society', 'Haritha Induwara', '0779063904', 'https://mydb.org', 'Colombo, Sri Lanka', 'Student database exploration and open source software initiatives.', 'https://linkedin.com/company/mydb', '', '', 'mydb_logo.png'),
('foss@sliit.lk', 'SLIIT FOSS Community', 'Open Source Community', 'Tharindu Gamage', '0761122334', 'https://foss.sliit.lk', 'New Kandy Road, Malabe', 'Promoting free and open-source software culture among university undergraduates.', 'https://linkedin.com/company/sliit-foss', 'https://twitter.com/sliitfoss', 'https://facebook.com/sliitfoss', NULL),
('ieee@ucsc.cmb.ac.lk', 'IEEE Student Branch UCSC', 'Academic Society', 'Kasun Rathnayake', '0715551234', 'https://ieee.ucsc.cmb.ac.lk', 'UCSC, Reid Avenue, Colombo 07', 'Empowering students to innovate and excel in technology and engineering disciplines.', 'https://linkedin.com/company/ieee-ucsc', 'https://twitter.com/ieee_ucsc', 'https://facebook.com/ieeeucsc', 'ieee_logo.png'),
('rotaract@ucsc.cmb.ac.lk', 'Rotaract Club of UCSC', 'Community Service', 'Sachini Wickramasinghe', '0784449876', 'https://rotaractucsc.org', 'Colombo 07, Sri Lanka', 'Youth leadership and social community empowerment initiatives across Sri Lanka.', 'https://linkedin.com/company/rotaract-ucsc', '', 'https://facebook.com/rotaractucsc', 'rotaract_logo.png');

-- --------------------------------------------------------

--
-- Table structure for table `org_teams`
--

DROP TABLE IF EXISTS `org_teams`;
CREATE TABLE `org_teams` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `leader_email` varchar(100) NOT NULL,
  `deadline` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ontrack',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `org_team_members`
--

DROP TABLE IF EXISTS `org_team_members`;
CREATE TABLE `org_team_members` (
  `team_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolio`
--

INSERT INTO `portfolio` (`portfolio_id`, `Email`, `title`, `description`, `project_link`, `image`, `created_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'SkillBridge Platform', 'Modern role-based internship matching and student portfolio management system built with PHP and MySQL.', 'https://github.com/harithainduwara/Skill_Bridge', 'portfolio_skillbridge.png', '2026-08-20 10:00:00'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 'E-Commerce Microservices', 'High-performance microservices backend with product catalog, cart, and payment gateway integration.', 'https://github.com/harithainduwara/ecommerce-microservices', 'portfolio_ecommerce.png', '2026-08-20 10:30:00'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 'Skin Disease Detection with CNN', 'Deep learning mobile application that detects dermatological conditions from camera photos using Flutter and PyTorch.', 'https://github.com/kavinduperera/skin-disease-ai', 'portfolio_skindisease.png', '2026-08-21 09:00:00'),
(4, '2024is015@stu.ucsc.cmb.ac.lk', 'FinTech Digital Wallet UI/UX', 'Complete UX research, wireframing, design tokens, and interactive Figma prototyping for digital wallet application.', 'https://www.behance.net/gallery/fintech-app-redesign', 'portfolio_fintech.png', '2026-08-21 11:00:00'),
(5, '2024is032@stu.ucsc.cmb.ac.lk', 'Automated Network Security Scanner', 'Python CLI tool for security audits, port scanning, and CVE reporting using Nmap and Shodan APIs.', 'https://github.com/sahanw/vuln-scanner', 'portfolio_scanner.png', '2026-08-22 14:00:00'),
(6, '2024is044@stu.ucsc.cmb.ac.lk', 'Real-Time Kubernetes Analytics Dashboard', 'Kubernetes-deployed dashboard visualizing live streaming server metrics using React, Kafka, and Grafana.', 'https://github.com/dinithij/k8s-analytics-dashboard', 'portfolio_k8s.png', '2026-08-22 15:30:00'),
(7, '2024is091@stu.ucsc.cmb.ac.lk', 'Campus Bus Tracker', 'Flutter app that shows university shuttle buses on a live map with arrival alerts, built with Firebase Realtime Database.', 'https://github.com/sachiniw/campus-bus-tracker', NULL, '2026-08-23 09:00:00'),
(8, '2024is091@stu.ucsc.cmb.ac.lk', 'StudyBuddy Flashcards', 'Offline-first flashcard app with spaced repetition and dark mode, published as an internal beta for classmates.', 'https://github.com/sachiniw/studybuddy', NULL, '2026-08-23 09:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `organization_email` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `icon` varchar(50) DEFAULT '?',
  `description` text DEFAULT NULL,
  `learning_objectives` text DEFAULT NULL,
  `expected_outcomes` text DEFAULT NULL,
  `difficulty` varchar(30) DEFAULT 'Intermediate',
  `duration_weeks` int(11) DEFAULT NULL,
  `members` int(11) DEFAULT 1,
  `preferred_year` varchar(20) DEFAULT 'Any Year',
  `deadline` date DEFAULT NULL,
  `visibility` varchar(20) DEFAULT 'Public',
  `status` varchar(30) DEFAULT 'reviewing',
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `organization_email`, `category_id`, `title`, `icon`, `description`, `learning_objectives`, `expected_outcomes`, `difficulty`, `duration_weeks`, `members`, `preferred_year`, `deadline`, `visibility`, `status`, `posted_at`) VALUES
(1, '2024is052@stu.ucsc.cmb.ac.lk', 6, 'AI Model Optimization', '📊', 'Optimizing large deep learning transformer models for edge devices and mobile browsers.', 'Learn quantization, model pruning, ONNX runtime optimization.', 'Production ready lightweight inference model with <50ms latency.', 'Intermediate', 12, 4, 'Any Year', '2026-10-20', 'Public', 'reviewing', '2026-08-17 13:46:49'),
(2, '2024is052@stu.ucsc.cmb.ac.lk', 2, 'Cloud Migration UI/UX', '🎨', 'Modernizing legacy university student portal interfaces into accessible, responsive cloud UI.', 'Master accessible design principles (WCAG 2.1) and component library architecture.', 'Comprehensive Figma design system and React component kit.', 'Intermediate', 8, 2, 'Any Year', '2026-11-15', 'Public', 'reviewing', '2026-08-17 13:46:49'),
(3, 'ieee@ucsc.cmb.ac.lk', 3, 'Smart Campus Energy Monitor', '⚡', 'Building IoT power meters across faculty computer labs to track and minimize electricity waste in real-time.', 'Gain hands-on experience with microcontroller programming, MQTT broker protocols, and sensor calibration.', 'Operational live campus energy dashboard and automated alert system for abnormal power surges.', 'Advanced', 16, 5, '3rd Year', '2026-12-10', 'Public', 'reviewing', '2026-08-25 08:30:00'),
(4, 'rotaract@ucsc.cmb.ac.lk', 4, 'Open Source Blood Donation Portal', '❤️', 'A streamlined digital platform connecting volunteer blood donors with regional hospitals during critical shortages.', 'Build secure role-based portals, integrate SMS alerts, and implement geolocation search.', 'Fully tested web application deployed for nationwide donor registration drives.', 'Beginner', 8, 3, '1st Year', '2026-11-01', 'Public', 'reviewing', '2026-08-28 05:00:00'),
(5, 'foss@sliit.lk', 5, 'FOSS Contributor Leaderboard', '🚀', 'Gamified leaderboard tracking pull requests, commits, and reviews of university students contributing to open source repositories.', 'Work with GraphQL APIs, asynchronous queue processing in Go, and modern frontend dashboards.', 'Automated ranking system used for annual Hacktoberfest and code sprint awards.', 'Intermediate', 12, 4, '2nd Year', '2026-11-25', 'Public', 'reviewing', '2026-09-01 10:30:00'),
(6, 'foss@sliit.lk', 6, 'AI Chatbot for Student Support', '🤖', 'A conversational chatbot that answers common student queries about courses, deadlines and campus services.', 'Build intent-classification pipelines and integrate an NLP model into a live Flask backend.', 'A deployed chatbot handling FAQs for the student portal.', 'Intermediate', 8, 4, 'Any Year', '2026-10-14', 'Public', 'inprogress', '2026-09-23 04:30:00'),
(7, 'foss@sliit.lk', 7, 'Mobile Attendance Tracker', '📱', 'A mobile app that lets lecturers mark attendance via QR code scans, synced live to Firebase.', 'Learn cross-platform Flutter development and real-time Firebase database sync.', 'A working attendance app tested in at least one live class.', 'Beginner', 8, 2, 'Any Year', '2026-10-09', 'Public', 'inprogress', '2026-09-22 04:30:00'),
(8, 'foss@sliit.lk', 4, 'Portfolio Website Builder', '💼', 'A drag-and-drop builder that lets students generate a personal portfolio site from a template.', 'Practice component-driven React development and utility-first CSS with Tailwind.', 'A reusable portfolio template students can fork and deploy.', 'Beginner', 4, 3, 'Any Year', '2026-10-19', 'Public', 'inprogress', '2026-09-21 04:30:00'),
(9, 'foss@sliit.lk', 4, 'Campus Event Management System', '🎪', 'A system for clubs to publish campus events, manage RSVPs and check in attendees at the door.', 'Work with Laravel MVC structure, MySQL relational design, and RSVP/ticketing logic.', 'A working event registration and check-in system used for one real campus event.', 'Intermediate', 8, 3, '2nd Year', '2026-09-29', 'Public', 'completed', '2026-09-20 04:30:00'),
(10, 'ieee@ucsc.cmb.ac.lk', 8, 'University Research Collaboration Platform', '🔬', 'A collaborative platform for university students and researchers to manage research projects, tasks, documents and team collaboration.', 'Learn REST API development, database design, authentication and collaborative system development.', 'Working research collaboration platform with project management and document sharing features.', 'Intermediate', 16, 4, '2nd Year', '2027-01-15', 'Public', 'reviewing', '2026-09-24 13:20:56'),
(11, 'foss@sliit.lk', 3, 'Smart Agriculture Monitoring System', '🌱', 'An IoT-based agricultural monitoring system that collects soil moisture, temperature and humidity data and displays real-time information through a web dashboard.', 'Learn IoT sensor integration, MQTT communication, cloud data storage and real-time monitoring.', 'Functional smart agriculture monitoring system with a real-time sensor dashboard and automated alerts.', 'Advanced', 3, 6, 'Any Year', '2026-12-20', 'Public', 'reviewing', '2026-09-24 13:20:56'),
(12, 'foss@sliit.lk', 7, 'Campus Ride-Sharing App', '🚗', 'A carpooling app for students to share rides to and from campus.', 'Build a React Native app with live location tracking and a Node.js matching backend.', 'A working ride-matching prototype for campus commutes.', 'Intermediate', 8, 3, 'Any Year', '2026-11-05', 'Public', 'rejected', '2026-09-18 04:30:00'),
(15, 'foss@sliit.lk', 10, 'Crypto Trading Signal Bot', '🤖', 'A bot that reads crypto market data and sends buy/sell signals to a Telegram group.', 'Working with REST APIs, basic data analysis, bot development.', 'A working Telegram bot that posts automated trading signals.', 'Intermediate', 6, 3, 'Any Year', '2026-11-30', 'Public', 'rejected', '2026-09-23 18:29:00'),
(17, 'foss@sliit.lk', 11, 'Smart Clinic Queue & Appointment System', '🏥', 'We are partnering with Malabe Community Clinic to replace their paper token system with an online queue and appointment platform. Patients can book a time slot, get an SMS when their turn is close, and doctors can see the live queue on a dashboard.', 'Build a full-stack Laravel application, design a real-time queue system, integrate SMS notifications with Twilio, and create analytics dashboards with Chart.js.', 'A production-ready queue and appointment system deployed at the clinic.', 'Advanced', 12, 4, 'Year 3', '2026-11-15', 'Public', 'hold', '2026-09-21 18:29:00');

-- --------------------------------------------------------

--
-- Table structure for table `project_applications`
--

DROP TABLE IF EXISTS `project_applications`;
CREATE TABLE `project_applications` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `decided_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_applications`
--

INSERT INTO `project_applications` (`id`, `project_id`, `Email`, `status`, `applied_at`, `decided_at`) VALUES
(5, 12, '2024is058@stu.ucsc.cmb.ac.lk', 'accepted', '2026-09-21 07:50:04', NULL),
(6, 12, '2024is078@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-22 07:50:04', NULL),
(7, 12, '2024is032@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-23 07:50:04', NULL),
(10, 6, '2024is044@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-13 07:56:23', NULL),
(11, 6, '2024is078@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-14 07:56:23', NULL),
(12, 7, '2024is058@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-15 07:56:23', NULL),
(13, 7, '2024is015@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-16 07:56:23', NULL),
(14, 7, '2024is032@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-17 07:56:23', NULL),
(15, 8, '2024is001@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-18 07:56:23', NULL),
(16, 8, '2024is058@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-19 07:56:23', NULL),
(17, 8, '2024is078@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-20 07:56:23', NULL),
(18, 9, '2024is015@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-21 07:56:23', NULL),
(19, 9, '2024is032@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-22 07:56:23', NULL),
(20, 9, '2024is078@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-23 07:56:23', NULL),
(21, 12, '2024is001@stu.ucsc.cmb.ac.lk', 'pending', '2026-09-24 07:56:23', NULL),
(51, 9, '2024is044@stu.ucsc.cmb.ac.lk', 'accepted', '2026-09-26 15:49:23', '2026-09-26 15:49:23'),
(52, 6, '2024is032@stu.ucsc.cmb.ac.lk', 'accepted', '2026-09-26 17:11:57', '2026-09-26 17:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `project_categories`
--

DROP TABLE IF EXISTS `project_categories`;
CREATE TABLE `project_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_categories`
--

INSERT INTO `project_categories` (`category_id`, `category_name`) VALUES
(6, 'AI / Machine Learning'),
(5, 'DevOps & Tooling'),
(10, 'FinTech'),
(11, 'HealthTech'),
(3, 'Internet of Things'),
(7, 'Mobile Development'),
(8, 'Research & Innovation'),
(2, 'UI/UX Design'),
(4, 'Web Development');

-- --------------------------------------------------------

--
-- Table structure for table `project_documents`
--

DROP TABLE IF EXISTS `project_documents`;
CREATE TABLE `project_documents` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_feedback`
--

DROP TABLE IF EXISTS `project_feedback`;
CREATE TABLE `project_feedback` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `technical` decimal(2,1) NOT NULL,
  `communication` decimal(2,1) NOT NULL,
  `teamwork` decimal(2,1) NOT NULL,
  `problem_solving` decimal(2,1) NOT NULL,
  `summary` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_moderation_logs`
--

DROP TABLE IF EXISTS `project_moderation_logs`;
CREATE TABLE `project_moderation_logs` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `action_type` enum('Hold','Rejected','Suspended','Active') NOT NULL DEFAULT 'Hold',
  `reason` text NOT NULL,
  `org_response` text DEFAULT NULL,
  `status` enum('Open','Resolved','Dismissed') NOT NULL DEFAULT 'Open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_moderation_logs`
--

INSERT INTO `project_moderation_logs` (`id`, `project_id`, `admin_email`, `action_type`, `reason`, `org_response`, `status`, `created_at`, `resolved_at`) VALUES
(1, 17, 'skillbridge62@gmail.com', 'Hold', 'This project looks valuable, but it breaks 4 SkillBridge project posting rules. Please fix them and request activation again.\r\n\r\n1) Real personal / medical data (Data Privacy rule)\r\nStudents cannot be given real patient records, NIC numbers or medical history. Use anonymised or sample (dummy) data only.\r\n\r\n2) Fees from students (Free Participation rule)\r\nProjects on SkillBridge must be free for students. Remove the LKR 2,500 registration fee.\r\n\r\n3) Contact outside the platform (Communication rule)\r\nPersonal WhatsApp numbers and personal email addresses are not allowed in project posts.\r\n\r\n4) Portfolio rights (Student Learning rule)\r\nStudents must be allowed to show their work in their portfolio / CV.', NULL, 'Open', '2026-09-24 14:59:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_proposals`
--

DROP TABLE IF EXISTS `project_proposals`;
CREATE TABLE `project_proposals` (
  `proposal_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `proposal_title` varchar(255) NOT NULL,
  `target_role` varchar(100) NOT NULL,
  `introduction` text NOT NULL,
  `solution_approach` text NOT NULL,
  `portfolio_attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','accepted','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `feedback_message` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_proposals`
--

INSERT INTO `project_proposals` (`proposal_id`, `project_id`, `student_email`, `proposal_title`, `target_role`, `introduction`, `solution_approach`, `portfolio_attachment`, `status`, `feedback_message`, `submitted_at`, `reviewed_at`) VALUES
(1, 6, '2024is032@stu.ucsc.cmb.ac.lk', 'Context-Aware Campus FAQ Bot', 'AI Engineer', 'I am an information systems undergraduate experienced with Python NLP pipelines and conversational bots.', 'I will build an intent-matching Flask REST API integrating spaCy and SQLite caching with response times under 200ms.', 'portfolio_scanner.png', 'accepted', 'Great proposal! Welcome to the project. We will add you to a team soon.', '2026-09-25 18:00:00', '2026-09-26 17:11:57'),
(2, 6, '2024is058@stu.ucsc.cmb.ac.lk', 'Conversational Assistant Integration', 'Backend Developer', 'Experienced full-stack developer eager to connect NLP models to scalable REST endpoints.', 'Design robust REST APIs in Flask with rate limiting and automated unit test suites.', 'portfolio_skillbridge.png', 'accepted', 'Solid backend architecture proposal.', '2026-09-22 09:15:00', '2026-09-23 04:30:00'),
(3, 11, '2024is015@stu.ucsc.cmb.ac.lk', 'Sensor Dashboard Accessibility Redesign', 'UI/UX Designer', 'Specialized in UI/UX and design systems with WCAG 2.1 compliance experience.', 'Create a high-contrast responsive dashboard in Figma and React for real-time soil and temperature readings.', 'portfolio_fintech.png', 'rejected', 'Your skills don’t match the project requirements.', '2026-09-26 04:30:00', '2026-09-26 15:17:29'),
(4, 3, '2024is058@stu.ucsc.cmb.ac.lk', 'Smart Lab Energy Monitor UI', 'Full-Stack Developer', 'Passionate about clean dashboard interfaces and live energy metric tracking.', 'Integrate MQTT stream with Node.js and Grafana visualization boards.', 'portfolio_skillbridge.png', 'pending', NULL, '2026-09-26 08:30:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_skills`
--

DROP TABLE IF EXISTS `project_skills`;
CREATE TABLE `project_skills` (
  `project_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_skills`
--

INSERT INTO `project_skills` (`project_id`, `skill_name`) VALUES
(1, 'AI'),
(1, 'AWS'),
(1, 'Machine Learning'),
(1, 'Model Pruning'),
(1, 'Python'),
(1, 'TensorFlow'),
(2, 'Design Systems'),
(2, 'Figma'),
(2, 'Prototyping'),
(2, 'React'),
(2, 'Tailwind'),
(2, 'User Research'),
(3, 'ESP32'),
(3, 'Grafana'),
(3, 'Green Tech'),
(3, 'IoT'),
(3, 'MQTT'),
(3, 'Node.js'),
(3, 'Sensors'),
(4, 'Bootstrap'),
(4, 'Emergency Alerts'),
(4, 'Healthcare'),
(4, 'MySQL'),
(4, 'PHP'),
(4, 'SMS Gateway'),
(4, 'Twilio'),
(5, 'DevOps'),
(5, 'GitHub GraphQL API'),
(5, 'Go'),
(5, 'Hacktoberfest'),
(5, 'Next.js'),
(5, 'Open Source'),
(6, 'AI'),
(6, 'Chatbot'),
(6, 'Flask'),
(6, 'NLP'),
(6, 'Python'),
(7, 'Firebase'),
(7, 'Flutter'),
(7, 'QR Code'),
(8, 'Portfolio'),
(8, 'React'),
(8, 'Tailwind CSS'),
(9, 'Events'),
(9, 'Laravel'),
(9, 'MySQL'),
(9, 'Ticketing'),
(10, 'Docker'),
(10, 'Node.js'),
(10, 'PostgreSQL'),
(10, 'React'),
(10, 'Research'),
(10, 'REST API'),
(11, 'ESP32'),
(11, 'Firebase'),
(11, 'IoT'),
(11, 'MQTT'),
(11, 'Sensors'),
(11, 'Smart Agriculture'),
(12, 'Carpooling'),
(12, 'Maps API'),
(12, 'Node.js'),
(12, 'React Native'),
(15, 'Binance API'),
(15, 'FinTech'),
(15, 'Python'),
(15, 'Telegram Bot API'),
(17, 'Chart.js'),
(17, 'HealthTech'),
(17, 'Laravel'),
(17, 'MySQL'),
(17, 'Twilio SMS');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_skills`
--

DROP TABLE IF EXISTS `proposal_skills`;
CREATE TABLE `proposal_skills` (
  `proposal_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposal_skills`
--

INSERT INTO `proposal_skills` (`proposal_id`, `skill_name`) VALUES
(1, 'Flask'),
(1, 'NLP'),
(1, 'Python'),
(2, 'Flask'),
(2, 'Python'),
(2, 'REST API'),
(3, 'Figma'),
(3, 'React'),
(3, 'UI/UX Research'),
(4, 'Grafana'),
(4, 'MQTT'),
(4, 'Node.js');

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
  `initial_experience` int(11) DEFAULT 0,
  `percentage` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skill_id`, `Email`, `skill_name`, `category`, `level`, `initial_experience`, `percentage`, `created_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'React', 'Frontend', 'Advanced', 2, 80, '2026-09-22 08:00:00'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 'PHP', 'Backend', 'Expert', 3, 95, '2026-09-22 08:00:00'),
(3, '2024is058@stu.ucsc.cmb.ac.lk', 'MySQL', 'Database', 'Advanced', 2, 80, '2026-09-22 08:00:00'),
(4, '2024is078@stu.ucsc.cmb.ac.lk', 'Docker', 'Cloud & DevOps', 'Intermediate', 1, 60, '2026-09-22 08:00:00'),
(5, '2024is078@stu.ucsc.cmb.ac.lk', 'Python', 'Programming', 'Expert', 3, 95, '2026-09-22 08:00:00'),
(6, '2024is078@stu.ucsc.cmb.ac.lk', 'Flutter', 'Mobile Development', 'Advanced', 2, 80, '2026-09-22 08:00:00'),
(7, '2024is078@stu.ucsc.cmb.ac.lk', 'PyTorch', 'AI & Machine Learning', 'Intermediate', 1, 60, '2026-09-22 08:00:00'),
(8, '2024is015@stu.ucsc.cmb.ac.lk', 'Figma', 'Web Design', 'Expert', 3, 95, '2026-09-22 08:00:00'),
(9, '2024is015@stu.ucsc.cmb.ac.lk', 'UI/UX Research', 'Web Design', 'Advanced', 2, 80, '2026-09-22 08:00:00'),
(10, '2024is015@stu.ucsc.cmb.ac.lk', 'CSS / Sass', 'Frontend', 'Expert', 3, 95, '2026-09-22 08:00:00'),
(11, '2024is032@stu.ucsc.cmb.ac.lk', 'Network Security', 'Cloud & DevOps', 'Intermediate', 1, 60, '2026-09-22 08:00:00'),
(12, '2024is032@stu.ucsc.cmb.ac.lk', 'Linux System Administration', 'Cloud & DevOps', 'Advanced', 2, 80, '2026-09-22 08:00:00'),
(13, '2024is001@stu.ucsc.cmb.ac.lk', 'Kubernetes', 'Cloud & DevOps', 'Intermediate', 1, 60, '2026-09-22 08:00:00'),
(14, '2024is044@stu.ucsc.cmb.ac.lk', 'Java', 'Backend', 'Beginner', 2, 40, '2026-09-22 08:00:00'),
(15, '2024is091@stu.ucsc.cmb.ac.lk', 'Flutter', 'Mobile Development', 'Advanced', 2, 80, '2026-09-25 10:20:10'),
(16, '2024is091@stu.ucsc.cmb.ac.lk', 'Firebase', 'Cloud & DevOps', 'Advanced', 2, 75, '2026-09-25 10:20:10'),
(17, '2024is091@stu.ucsc.cmb.ac.lk', 'Kotlin', 'Mobile Development', 'Intermediate', 1, 60, '2026-09-25 10:20:10'),
(18, '2024is091@stu.ucsc.cmb.ac.lk', 'Figma', 'Web Design', 'Intermediate', 1, 55, '2026-09-25 10:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `skill_categories`
--

DROP TABLE IF EXISTS `skill_categories`;
CREATE TABLE `skill_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skill_categories`
--

INSERT INTO `skill_categories` (`category_id`, `category_name`) VALUES
(7, 'AI & Machine Learning'),
(2, 'Backend'),
(4, 'Cloud & DevOps'),
(3, 'Database'),
(1, 'Frontend'),
(6, 'Mobile Development'),
(5, 'Programming'),
(8, 'Web Design');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE `student` (
  `Email` varchar(100) NOT NULL,
  `domain` varchar(100) DEFAULT NULL,
  `degree_id` int(11) DEFAULT NULL,
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

INSERT INTO `student` (`Email`, `domain`, `degree_id`, `University`, `year`, `degree`, `Name`, `profile_image`, `bio`, `github`, `linkedin`, `website`, `profile_completion`) VALUES
('2024is001@stu.ucsc.cmb.ac.lk', 'stu.ucsc.cmb.ac.lk', 1, 'University OF Colombo', '2', 'B.Sc. in Computer Science', 'Kavindu Perera', NULL, 'Mobile App Developer and Machine Learning enthusiast with experience in Flutter and PyTorch.', 'https://github.com/kavinduperera', 'https://linkedin.com/in/kavindu-perera', 'https://kavindu.me', NULL),
('2024is015@stu.ucsc.cmb.ac.lk', 'stu.ucsc.cmb.ac.lk', 2, 'University OF Colombo', '3', 'B.Sc. in Software Engineering', 'Nimasha Fernando', NULL, 'UI/UX Designer and Frontend Specialist focused on intuitive user experiences and design systems.', 'https://github.com/nimasha-fernando', 'https://linkedin.com/in/nimashafernando', 'https://nimasha.design', NULL),
('2024is032@stu.ucsc.cmb.ac.lk', 'stu.ucsc.cmb.ac.lk', 3, 'University OF Colombo', '1', 'B.Sc. in Information Systems', 'Sahan Wickramasinghe', NULL, 'Cybersecurity student and backend explorer, working with Python, Linux, and network security.', 'https://github.com/sahanw', 'https://linkedin.com/in/sahan-wickrama', 'https://sahan.tech', NULL),
('2024is044@stu.ucsc.cmb.ac.lk', 'stu.ucsc.cmb.ac.lk', 1, 'University OF Colombo', '3', 'B.Sc. in Computer Science', 'Dinithi Jayawardena', NULL, 'Data Science & DevOps enthusiast. Passionate about Docker, Kubernetes, and predictive analytics.', 'https://github.com/dinithij', 'https://linkedin.com/in/dinithi-jayawardena', 'https://dinithi.io', NULL),
('2024is058@stu.ucsc.cmb.ac.lk', 'stu.ucsc.cmb.ac.lk', 3, 'University OF Colombo', '2', 'B.Sc. in Information Systems', 'Haritha Induwara', NULL, 'Aspiring Full-Stack Developer & Cloud Enthusiast passionate about building scalable web solutions.', 'https://github.com/harithainduwara', 'https://linkedin.com/in/harithainduwara', 'https://harithainduwara.dev', NULL),
('2024is078@stu.ucsc.cmb.ac.lk', 'stu.ucsc.cmb.ac.lk', 3, 'University OF Colombo', '3', 'B.Sc. in Information Systems', 'Lasith Perera', NULL, 'Machine Learning enthusiast. Passionate about Docker, Kubernetes, and predictive analytics.', 'https://github.com/lasith-perera', 'https://linkedin.com/in/lasith-perera', 'https://lasith.dev', NULL),
('2024is091@stu.ucsc.cmb.ac.lk', 'stu.ucsc.cmb.ac.lk', 2, 'University OF Colombo', '3', 'B.Sc. in Software Engineering', 'Sachini Wijesinghe', NULL, 'Mobile app developer who loves building clean, offline-first Flutter apps with Firebase. Interested in EdTech and accessibility.', 'https://github.com/sachiniw', 'https://linkedin.com/in/sachini-wijesinghe', 'https://sachini.dev', NULL),
('harithainduwara05@gmail.com', 'sliit.lk', 4, 'SLIIT - Faculty of Computing', '2024', 'B.Sc. in Information Systems', 'Haritha Induwara', NULL, NULL, NULL, NULL, NULL, 0),
('lasithperera2004@gmail.com', 'stu.ucsc.cmb.ac.lk', 3, 'University of Colombo - School Of Computing', '2024', 'B.Sc. in Information Systems', 'Haritha Induwara', NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `student_internal_portfolio_overrides`
--

DROP TABLE IF EXISTS `student_internal_portfolio_overrides`;
CREATE TABLE `student_internal_portfolio_overrides` (
  `student_project_id` int(11) NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `display_title` varchar(150) DEFAULT NULL,
  `display_description` text DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_internal_portfolio_overrides`
--

INSERT INTO `student_internal_portfolio_overrides` (`student_project_id`, `student_email`, `display_title`, `display_description`, `hidden`) VALUES
(5, '2024is015@stu.ucsc.cmb.ac.lk', 'Cloud UI/UX & Micro-Frontend Design System', 'Architected a 40+ component Figma design system and implemented reusable, WCAG 2.1 accessible React UI components for the university portal migration.', 0),
(6, '2024is032@stu.ucsc.cmb.ac.lk', 'IoT Campus Energy Telemetry Architecture', 'Secured MQTT broker network communications and designed data ingestion pipelines for real-time sensor node power monitoring.', 0),
(30, '2024is078@stu.ucsc.cmb.ac.lk', 'Realtime QR Attendance Cloud Sync Engine', 'Engineered Firestore security rules, Firebase Cloud Functions, and local SQLite caching for fast offline attendance tracking during campus lectures.', 0),
(33, '2024is058@stu.ucsc.cmb.ac.lk', 'Campus Event Ticketing & Registration API', 'Developed RESTful ticketing, seat reservation, and attendee check-in services with QR verification and automatic email dispatch.', 0),
(38, '2024is058@stu.ucsc.cmb.ac.lk', 'Carpooling Matchmaking Prototype (Hidden Draft)', 'Early development prototype exploring ride-matching algorithms and geolocation coordinates.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_portfolio_projects`
--

DROP TABLE IF EXISTS `student_portfolio_projects`;
CREATE TABLE `student_portfolio_projects` (
  `portfolio_project_id` bigint(20) UNSIGNED NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `legacy_portfolio_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `repository_url` varchar(500) DEFAULT NULL,
  `project_image` varchar(255) DEFAULT NULL,
  `project_status` enum('Completed','Currently Working') NOT NULL DEFAULT 'Currently Working',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `student_portfolio_projects`
--

INSERT INTO `student_portfolio_projects` (`portfolio_project_id`, `student_email`, `legacy_portfolio_id`, `title`, `description`, `repository_url`, `project_image`, `project_status`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 1, 'SkillBridge Platform', 'Modern role-based internship matching and student portfolio management system built with PHP and MySQL.', 'https://github.com/harithainduwara/Skill_Bridge', 'portfolio_skillbridge.png', 'Currently Working', NULL, NULL, '2026-10-29 17:56:52', '2026-10-29 17:56:52'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 2, 'E-Commerce Microservices', 'High-performance microservices backend with product catalog, cart, and payment gateway integration.', 'https://github.com/harithainduwara/ecommerce-microservices', 'portfolio_ecommerce.png', 'Currently Working', NULL, NULL, '2026-10-29 17:56:52', '2026-10-29 17:56:52'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 3, 'Skin Disease Detection with CNN', 'Deep learning mobile application that detects dermatological conditions from camera photos using Flutter and PyTorch.', 'https://github.com/kavinduperera/skin-disease-ai', 'portfolio_skindisease.png', 'Currently Working', NULL, NULL, '2026-10-29 17:56:52', '2026-10-29 17:56:52'),
(4, '2024is015@stu.ucsc.cmb.ac.lk', 4, 'FinTech Digital Wallet UI/UX', 'Complete UX research, wireframing, design tokens, and interactive Figma prototyping for digital wallet application.', 'https://www.behance.net/gallery/fintech-app-redesign', 'portfolio_fintech.png', 'Currently Working', NULL, NULL, '2026-10-29 17:56:52', '2026-10-29 17:56:52'),
(5, '2024is032@stu.ucsc.cmb.ac.lk', 5, 'Automated Network Security Scanner', 'Python CLI tool for security audits, port scanning, and CVE reporting using Nmap and Shodan APIs.', 'https://github.com/sahanw/vuln-scanner', 'portfolio_scanner.png', 'Currently Working', NULL, NULL, '2026-10-29 17:56:52', '2026-10-29 17:56:52'),
(6, '2024is044@stu.ucsc.cmb.ac.lk', 6, 'Real-Time Kubernetes Analytics Dashboard', 'Kubernetes-deployed dashboard visualizing live streaming server metrics using React, Kafka, and Grafana.', 'https://github.com/dinithij/k8s-analytics-dashboard', 'portfolio_k8s.png', 'Currently Working', NULL, NULL, '2026-10-29 17:56:52', '2026-10-29 17:56:52'),
(7, '2024is091@stu.ucsc.cmb.ac.lk', 7, 'Campus Bus Tracker', 'Flutter app that shows university shuttle buses on a live map with arrival alerts, built with Firebase Realtime Database.', 'https://github.com/sachiniw/campus-bus-tracker', NULL, 'Currently Working', NULL, NULL, '2026-10-29 17:56:52', '2026-10-29 17:56:52'),
(8, '2024is091@stu.ucsc.cmb.ac.lk', 8, 'StudyBuddy Flashcards', 'Offline-first flashcard app with spaced repetition and dark mode, published as an internal beta for classmates.', 'https://github.com/sachiniw/studybuddy', NULL, 'Currently Working', NULL, NULL, '2026-10-29 17:56:52', '2026-10-29 17:56:52');

-- --------------------------------------------------------

--
-- Table structure for table `student_portfolio_project_technologies`
--

DROP TABLE IF EXISTS `student_portfolio_project_technologies`;
CREATE TABLE `student_portfolio_project_technologies` (
  `portfolio_project_id` bigint(20) UNSIGNED NOT NULL,
  `technology_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_profile_views`
--

DROP TABLE IF EXISTS `student_profile_views`;
CREATE TABLE `student_profile_views` (
  `view_id` bigint(20) UNSIGNED NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profile_views`
--

INSERT INTO `student_profile_views` (`view_id`, `student_email`, `viewed_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', '2026-09-15 08:30:00'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', '2026-09-18 14:15:22'),
(3, '2024is058@stu.ucsc.cmb.ac.lk', '2026-09-22 10:45:10'),
(4, '2024is078@stu.ucsc.cmb.ac.lk', '2026-09-20 09:12:00'),
(5, '2024is078@stu.ucsc.cmb.ac.lk', '2026-09-21 16:30:45'),
(6, '2024is078@stu.ucsc.cmb.ac.lk', '2026-09-23 11:20:15'),
(7, '2024is001@stu.ucsc.cmb.ac.lk', '2026-09-19 13:05:00'),
(8, '2024is001@stu.ucsc.cmb.ac.lk', '2026-09-24 15:40:30'),
(9, '2024is015@stu.ucsc.cmb.ac.lk', '2026-09-21 10:10:00'),
(10, '2024is015@stu.ucsc.cmb.ac.lk', '2026-09-25 17:22:18'),
(11, '2024is044@stu.ucsc.cmb.ac.lk', '2026-09-22 12:00:00'),
(12, '2024is044@stu.ucsc.cmb.ac.lk', '2026-09-24 09:45:12'),
(13, '2024is032@stu.ucsc.cmb.ac.lk', '2026-09-23 18:30:00'),
(14, '2024is091@stu.ucsc.cmb.ac.lk', '2026-09-24 14:12:00'),
(15, 'harithainduwara05@gmail.com', '2026-09-25 08:00:00');

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
(7, '2024is044@stu.ucsc.cmb.ac.lk', 5, 'Backend & DevOps Engineer', 50, 'In Progress'),
(26, '2024is058@stu.ucsc.cmb.ac.lk', 6, 'NLP Engineer', 10, 'In Progress'),
(27, '2024is001@stu.ucsc.cmb.ac.lk', 6, 'Backend Developer', 10, 'In Progress'),
(28, '2024is015@stu.ucsc.cmb.ac.lk', 6, 'UI Designer', 10, 'In Progress'),
(29, '2024is001@stu.ucsc.cmb.ac.lk', 7, 'Flutter Developer', 15, 'In Progress'),
(30, '2024is078@stu.ucsc.cmb.ac.lk', 7, 'Firebase Engineer', 15, 'In Progress'),
(31, '2024is015@stu.ucsc.cmb.ac.lk', 8, 'Frontend Developer', 20, 'In Progress'),
(32, '2024is044@stu.ucsc.cmb.ac.lk', 8, 'UI Designer', 20, 'In Progress'),
(33, '2024is058@stu.ucsc.cmb.ac.lk', 9, 'Backend Developer', 100, 'Completed'),
(34, '2024is001@stu.ucsc.cmb.ac.lk', 9, 'Frontend Developer', 100, 'Completed'),
(35, '2024is044@stu.ucsc.cmb.ac.lk', 9, 'DB Engineer', 100, 'Completed'),
(38, '2024is058@stu.ucsc.cmb.ac.lk', 12, 'Mobile Developer', 5, 'In Progress');

-- --------------------------------------------------------

--
-- Table structure for table `student_resumes`
--

DROP TABLE IF EXISTS `student_resumes`;
CREATE TABLE `student_resumes` (
  `resume_id` int(11) NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `template_type` enum('modern','classic','minimal') NOT NULL DEFAULT 'modern',
  `resume_title` varchar(150) NOT NULL DEFAULT 'Professional Software Engineer CV',
  `custom_summary` text DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_resumes`
--

INSERT INTO `student_resumes` (`resume_id`, `student_email`, `template_type`, `resume_title`, `custom_summary`, `is_primary`, `updated_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 'modern', 'Full-Stack & Cloud Engineer CV', 'Results-driven undergraduate with hands-on experience in full-stack web architectures, microservices, and cloud deployment.', 1, '2026-09-25 14:00:00'),
(2, '2024is015@stu.ucsc.cmb.ac.lk', 'minimal', 'UI/UX Designer & Frontend CV', 'Creative UI/UX designer and frontend specialist dedicated to accessible design systems and intuitive user interfaces.', 1, '2026-09-24 16:30:00'),
(3, '2024is001@stu.ucsc.cmb.ac.lk', 'classic', 'Machine Learning & Mobile Dev CV', 'Passionate developer focusing on PyTorch deep learning models and cross-platform Flutter applications.', 1, '2026-09-25 11:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_resume_items`
--

DROP TABLE IF EXISTS `student_resume_items`;
CREATE TABLE `student_resume_items` (
  `id` int(11) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `item_type` enum('skill','portfolio_project','platform_project') NOT NULL,
  `item_reference_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_resume_items`
--

INSERT INTO `student_resume_items` (`id`, `resume_id`, `item_type`, `item_reference_id`) VALUES
(1, 1, 'skill', 1),
(2, 1, 'skill', 2),
(3, 1, 'skill', 3),
(4, 1, 'skill', 4),
(5, 1, 'portfolio_project', 1),
(6, 1, 'portfolio_project', 2),
(7, 2, 'skill', 8),
(8, 2, 'skill', 9),
(9, 2, 'portfolio_project', 4),
(10, 3, 'skill', 5),
(11, 3, 'skill', 7),
(12, 3, 'portfolio_project', 3);

-- --------------------------------------------------------

--
-- Table structure for table `student_saved_internships`
--

DROP TABLE IF EXISTS `student_saved_internships`;
CREATE TABLE `student_saved_internships` (
  `student_email` varchar(100) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_saved_internships`
--

INSERT INTO `student_saved_internships` (`student_email`, `internship_id`, `saved_at`) VALUES
('2024is001@stu.ucsc.cmb.ac.lk', 2, '2026-09-21 14:30:00'),
('2024is015@stu.ucsc.cmb.ac.lk', 1, '2026-09-20 09:15:00'),
('2024is058@stu.ucsc.cmb.ac.lk', 1, '2026-09-21 08:00:00'),
('2024is058@stu.ucsc.cmb.ac.lk', 2, '2026-09-22 10:30:00'),
('2024is058@stu.ucsc.cmb.ac.lk', 3, '2026-09-23 16:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_saved_projects`
--

DROP TABLE IF EXISTS `student_saved_projects`;
CREATE TABLE `student_saved_projects` (
  `student_email` varchar(100) NOT NULL,
  `project_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_saved_projects`
--

INSERT INTO `student_saved_projects` (`student_email`, `project_id`, `saved_at`) VALUES
('2024is001@stu.ucsc.cmb.ac.lk', 1, '2026-09-20 10:00:00'),
('2024is015@stu.ucsc.cmb.ac.lk', 2, '2026-09-21 16:20:00'),
('2024is058@stu.ucsc.cmb.ac.lk', 3, '2026-09-24 14:00:00'),
('2024is058@stu.ucsc.cmb.ac.lk', 4, '2026-09-23 11:15:00'),
('2024is058@stu.ucsc.cmb.ac.lk', 5, '2026-09-22 09:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_skills`
--

DROP TABLE IF EXISTS `student_skills`;
CREATE TABLE `student_skills` (
  `id` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `initial_experience` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_skills`
--

INSERT INTO `student_skills` (`id`, `Email`, `skill_id`, `initial_experience`, `created_at`) VALUES
(1, '2024is058@stu.ucsc.cmb.ac.lk', 1, 2, '2026-09-22 08:00:00'),
(2, '2024is058@stu.ucsc.cmb.ac.lk', 2, 3, '2026-09-22 08:00:00'),
(3, '2024is058@stu.ucsc.cmb.ac.lk', 3, 2, '2026-09-22 08:00:00'),
(4, '2024is058@stu.ucsc.cmb.ac.lk', 4, 1, '2026-09-22 08:00:00'),
(5, '2024is078@stu.ucsc.cmb.ac.lk', 5, 3, '2026-09-22 08:00:00'),
(6, '2024is078@stu.ucsc.cmb.ac.lk', 6, 2, '2026-09-22 08:00:00'),
(7, '2024is078@stu.ucsc.cmb.ac.lk', 7, 1, '2026-09-22 08:00:00'),
(8, '2024is015@stu.ucsc.cmb.ac.lk', 8, 3, '2026-09-22 08:00:00'),
(9, '2024is015@stu.ucsc.cmb.ac.lk', 9, 2, '2026-09-22 08:00:00'),
(10, '2024is015@stu.ucsc.cmb.ac.lk', 10, 3, '2026-09-22 08:00:00'),
(11, '2024is032@stu.ucsc.cmb.ac.lk', 11, 1, '2026-09-22 08:00:00'),
(12, '2024is032@stu.ucsc.cmb.ac.lk', 12, 2, '2026-09-22 08:00:00'),
(13, '2024is001@stu.ucsc.cmb.ac.lk', 13, 1, '2026-09-22 08:00:00'),
(14, '2024is044@stu.ucsc.cmb.ac.lk', 14, 2, '2026-09-22 08:00:00'),
(15, '2024is091@stu.ucsc.cmb.ac.lk', 6, 2, '2026-09-25 10:20:10'),
(16, '2024is091@stu.ucsc.cmb.ac.lk', 15, 2, '2026-09-25 10:20:10'),
(17, '2024is091@stu.ucsc.cmb.ac.lk', 16, 1, '2026-09-25 10:20:10'),
(18, '2024is091@stu.ucsc.cmb.ac.lk', 8, 1, '2026-09-25 10:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `student_technologies`
--

DROP TABLE IF EXISTS `student_technologies`;
CREATE TABLE `student_technologies` (
  `technology_id` int(10) UNSIGNED NOT NULL,
  `technology_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_deadline_changes`
--

DROP TABLE IF EXISTS `team_deadline_changes`;
CREATE TABLE `team_deadline_changes` (
  `change_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `old_deadline` date NOT NULL,
  `new_deadline` date NOT NULL,
  `reason` text NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_files`
--

DROP TABLE IF EXISTS `team_files`;
CREATE TABLE `team_files` (
  `file_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_final_reports`
--

DROP TABLE IF EXISTS `team_final_reports`;
CREATE TABLE `team_final_reports` (
  `report_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `submitted_by` varchar(100) NOT NULL,
  `original_name` varchar(255) NOT NULL DEFAULT '',
  `file_path` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `status` enum('submitted','accepted','changes_requested') NOT NULL DEFAULT 'submitted',
  `review_note` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_messages`
--

DROP TABLE IF EXISTS `team_messages`;
CREATE TABLE `team_messages` (
  `message_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `sender_email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_tasks`
--

DROP TABLE IF EXISTS `team_tasks`;
CREATE TABLE `team_tasks` (
  `task_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `assigned_to` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Todo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('SLIIT', 7, 'Faculty of Computing', 'sliit.lk', 'Hold', 'Malabe');

-- --------------------------------------------------------

--
-- Table structure for table `university_faculties`
--

DROP TABLE IF EXISTS `university_faculties`;
CREATE TABLE `university_faculties` (
  `domain` varchar(100) NOT NULL,
  `University` varchar(150) NOT NULL,
  `faculty` varchar(150) NOT NULL,
  `Location` varchar(150) DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `university_faculties`
--

INSERT INTO `university_faculties` (`domain`, `University`, `faculty`, `Location`, `Status`) VALUES
('eng.mrt.ac.lk', 'University of Moratuwa', 'Faculty of Engineering', 'Katubedda, Moratuwa', 'Active'),
('eng.pdn.ac.lk', 'University of Peradeniya', 'Faculty of Engineering', 'Peradeniya, Kandy', 'Active'),
('fas.sjp.ac.lk', 'University of Sri Jayewardenepura', 'Faculty of Applied Sciences', 'Nugegoda', 'Active'),
('fct.kln.ac.lk', 'University of Kelaniya', 'Faculty of Computing and Technology', 'Kelaniya', 'Active'),
('itfac.mrt.ac.lk', 'University of Moratuwa', 'Faculty of Information Technology', 'Katubedda, Moratuwa', 'Active'),
('sliit.lk', 'SLIIT', 'Faculty of Computing', 'Malabe', 'Hold'),
('stu.ucsc.cmb.ac.lk', 'University OF Colombo', 'School Of Computing', 'colombo 7', 'Active');

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
('2024is078@stu.ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'student', 'Active', '', '2026-09-22 02:24:58'),
('2024is091@stu.ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'student', 'Active', '', '2026-08-21 22:30:00'),
('admin.sarath@skillbridge.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'admin', 'Active', '', '2026-08-19 04:00:00'),
('careers@virtusa.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'company', 'Active', '', '2026-08-15 03:30:00'),
('foss@sliit.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'organization', 'Active', '', '2026-08-25 07:45:00'),
('harithainduwara05@gmail.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'student', 'Active', '115531', '2026-09-25 04:33:16'),
('hr@company.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'company', 'Active', '', '2026-07-05 13:00:00'),
('ieee@ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'organization', 'Active', '', '2026-08-22 06:15:00'),
('lasithperera2004@gmail.com', 'b72f270c135d0601e51aaa6962a05bc15eea2d50', 'student', 'Active', '', '2026-09-26 16:34:27'),
('recruitment@wso2.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'company', 'Active', '', '2026-08-16 05:00:00'),
('rotaract@ucsc.cmb.ac.lk', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'organization', 'Active', '', '2026-08-24 09:20:00'),
('skillbridge62@gmail.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'admin', 'Active', '', '2026-08-18 13:21:43'),
('talent@ifs.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'company', 'Active', '', '2026-08-17 08:30:00');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_internships_full`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_internships_full`;
CREATE TABLE `v_internships_full` (
`id` int(11)
,`title` varchar(255)
,`company_name` varchar(100)
,`company_email` varchar(100)
,`company_location` varchar(100)
,`company_logo` varchar(255)
,`industry_name` varchar(100)
,`logo_text` varchar(10)
,`logo_style` varchar(100)
,`duration_value` smallint(5) unsigned
,`duration_unit` enum('day','week','month','year')
,`duration_formatted` varchar(12)
,`deadline_date` date
,`deadline_formatted` varchar(41)
,`description` text
,`cover_image` varchar(255)
,`academic_year` varchar(50)
,`experience_level` varchar(50)
,`vacancies` int(11)
,`internship_type` varchar(50)
,`work_mode` varchar(50)
,`location` varchar(255)
,`start_date` date
,`paid_status` varchar(20)
,`stipend` decimal(10,2)
,`responsibilities` text
,`benefits` text
,`supporting_document` varchar(255)
,`created_at` timestamp
,`tech_tags` mediumtext
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_projects_full`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_projects_full`;
CREATE TABLE `v_projects_full` (
`id` int(11)
,`title` varchar(255)
,`company_name` varchar(100)
,`organization_email` varchar(100)
,`icon` varchar(50)
,`category_name` varchar(100)
,`difficulty` varchar(30)
,`duration_weeks` int(11)
,`members` int(11)
,`preferred_year` varchar(20)
,`deadline` date
,`visibility` varchar(20)
,`status` varchar(30)
,`posted_at` timestamp
,`description` text
,`learning_objectives` text
,`expected_outcomes` text
,`skills` mediumtext
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_student_profile_full`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_student_profile_full`;
CREATE TABLE `v_student_profile_full` (
`Email` varchar(100)
,`full_name` varchar(100)
,`academic_year` varchar(30)
,`degree_name` varchar(150)
,`faculty_name` varchar(150)
,`university_name` varchar(150)
,`campus_location` varchar(150)
,`profile_image` varchar(255)
,`bio` text
,`github` varchar(255)
,`linkedin` varchar(255)
,`website` varchar(255)
,`account_status` varchar(20)
,`registered_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_student_proposals_full`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_student_proposals_full`;
CREATE TABLE `v_student_proposals_full` (
`proposal_id` int(11)
,`project_id` int(11)
,`project_title` varchar(255)
,`organization_name` varchar(100)
,`student_email` varchar(100)
,`student_name` varchar(100)
,`proposal_title` varchar(255)
,`target_role` varchar(100)
,`introduction` text
,`solution_approach` text
,`portfolio_attachment` varchar(255)
,`proposal_status` enum('pending','accepted','rejected','withdrawn')
,`feedback_message` text
,`submitted_at` timestamp
,`reviewed_at` datetime
,`technologies` mediumtext
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_student_skills_computed`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_student_skills_computed`;
CREATE TABLE `v_student_skills_computed` (
`id` int(11)
,`student_email` varchar(100)
,`skill_name` varchar(100)
,`category_name` varchar(100)
,`initial_experience` int(11)
,`created_at` timestamp
,`total_experience_years` bigint(22)
,`level` varchar(12)
,`percentage` int(2)
);

-- --------------------------------------------------------

--
-- Structure for view `v_internships_full`
--
DROP TABLE IF EXISTS `v_internships_full`;

DROP VIEW IF EXISTS `v_internships_full`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_internships_full`  AS SELECT `i`.`id` AS `id`, `i`.`title` AS `title`, `c`.`Name` AS `company_name`, `i`.`company_email` AS `company_email`, `c`.`location` AS `company_location`, `c`.`profile_img` AS `company_logo`, `ind`.`industry_name` AS `industry_name`, `i`.`logo_text` AS `logo_text`, `i`.`logo_style` AS `logo_style`, `i`.`duration_value` AS `duration_value`, `i`.`duration_unit` AS `duration_unit`, concat(`i`.`duration_value`,' ',`i`.`duration_unit`,if(`i`.`duration_value` > 1,'s','')) AS `duration_formatted`, `i`.`deadline_date` AS `deadline_date`, date_format(`i`.`deadline_date`,'%b %d, %Y') AS `deadline_formatted`, `i`.`description` AS `description`, `i`.`cover_image` AS `cover_image`, `i`.`academic_year` AS `academic_year`, `i`.`experience_level` AS `experience_level`, `i`.`vacancies` AS `vacancies`, `i`.`internship_type` AS `internship_type`, `i`.`work_mode` AS `work_mode`, `i`.`location` AS `location`, `i`.`start_date` AS `start_date`, `i`.`paid_status` AS `paid_status`, `i`.`stipend` AS `stipend`, `i`.`responsibilities` AS `responsibilities`, `i`.`benefits` AS `benefits`, `i`.`supporting_document` AS `supporting_document`, `i`.`created_at` AS `created_at`, (select group_concat(`t`.`tag_name` order by `t`.`tag_name` ASC separator ', ') from (`internship_tech_tag_map` `tm` join `internship_tech_tags` `t` on(`tm`.`tag_id` = `t`.`tag_id`)) where `tm`.`internship_id` = `i`.`id`) AS `tech_tags` FROM ((`internships` `i` left join `company` `c` on(`i`.`company_email` = `c`.`Email`)) left join `internship_industries` `ind` on(`i`.`industry_id` = `ind`.`industry_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_projects_full`
--
DROP TABLE IF EXISTS `v_projects_full`;

DROP VIEW IF EXISTS `v_projects_full`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_projects_full`  AS SELECT `p`.`id` AS `id`, `p`.`title` AS `title`, `o`.`Name` AS `company_name`, `p`.`organization_email` AS `organization_email`, `p`.`icon` AS `icon`, `c`.`category_name` AS `category_name`, `p`.`difficulty` AS `difficulty`, `p`.`duration_weeks` AS `duration_weeks`, `p`.`members` AS `members`, `p`.`preferred_year` AS `preferred_year`, `p`.`deadline` AS `deadline`, `p`.`visibility` AS `visibility`, `p`.`status` AS `status`, `p`.`posted_at` AS `posted_at`, `p`.`description` AS `description`, `p`.`learning_objectives` AS `learning_objectives`, `p`.`expected_outcomes` AS `expected_outcomes`, (select group_concat(`project_skills`.`skill_name` separator ', ') from `project_skills` where `project_skills`.`project_id` = `p`.`id`) AS `skills` FROM ((`projects` `p` left join `organization` `o` on(`p`.`organization_email` = `o`.`Email`)) left join `project_categories` `c` on(`p`.`category_id` = `c`.`category_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_student_profile_full`
--
DROP TABLE IF EXISTS `v_student_profile_full`;

DROP VIEW IF EXISTS `v_student_profile_full`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_student_profile_full`  AS SELECT `s`.`Email` AS `Email`, `s`.`Name` AS `full_name`, `s`.`year` AS `academic_year`, `d`.`degree_name` AS `degree_name`, `uf`.`faculty` AS `faculty_name`, `uf`.`University` AS `university_name`, `uf`.`Location` AS `campus_location`, `s`.`profile_image` AS `profile_image`, `s`.`bio` AS `bio`, `s`.`github` AS `github`, `s`.`linkedin` AS `linkedin`, `s`.`website` AS `website`, `u`.`status` AS `account_status`, `u`.`created_at` AS `registered_at` FROM (((`student` `s` join `user` `u` on(`s`.`Email` = `u`.`Email`)) left join `degrees` `d` on(`s`.`degree_id` = `d`.`degree_id`)) left join `university_faculties` `uf` on(`s`.`domain` = `uf`.`domain`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_student_proposals_full`
--
DROP TABLE IF EXISTS `v_student_proposals_full`;

DROP VIEW IF EXISTS `v_student_proposals_full`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_student_proposals_full`  AS SELECT `pp`.`proposal_id` AS `proposal_id`, `pp`.`project_id` AS `project_id`, `p`.`title` AS `project_title`, `o`.`Name` AS `organization_name`, `pp`.`student_email` AS `student_email`, `s`.`Name` AS `student_name`, `pp`.`proposal_title` AS `proposal_title`, `pp`.`target_role` AS `target_role`, `pp`.`introduction` AS `introduction`, `pp`.`solution_approach` AS `solution_approach`, `pp`.`portfolio_attachment` AS `portfolio_attachment`, `pp`.`status` AS `proposal_status`, `pp`.`feedback_message` AS `feedback_message`, `pp`.`submitted_at` AS `submitted_at`, `pp`.`reviewed_at` AS `reviewed_at`, (select group_concat(`proposal_skills`.`skill_name` separator ', ') from `proposal_skills` where `proposal_skills`.`proposal_id` = `pp`.`proposal_id`) AS `technologies` FROM (((`project_proposals` `pp` join `projects` `p` on(`pp`.`project_id` = `p`.`id`)) join `organization` `o` on(`p`.`organization_email` = `o`.`Email`)) join `student` `s` on(`pp`.`student_email` = `s`.`Email`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_student_skills_computed`
--
DROP TABLE IF EXISTS `v_student_skills_computed`;

DROP VIEW IF EXISTS `v_student_skills_computed`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_student_skills_computed`  AS SELECT `ss`.`id` AS `id`, `ss`.`Email` AS `student_email`, `ms`.`skill_name` AS `skill_name`, `sc`.`category_name` AS `category_name`, `ss`.`initial_experience` AS `initial_experience`, `ss`.`created_at` AS `created_at`, `ss`.`initial_experience`+ timestampdiff(YEAR,`ss`.`created_at`,current_timestamp()) AS `total_experience_years`, CASE WHEN `ss`.`initial_experience` + timestampdiff(YEAR,`ss`.`created_at`,current_timestamp()) <= 1 THEN 'Beginner' WHEN `ss`.`initial_experience` + timestampdiff(YEAR,`ss`.`created_at`,current_timestamp()) <= 3 THEN 'Intermediate' WHEN `ss`.`initial_experience` + timestampdiff(YEAR,`ss`.`created_at`,current_timestamp()) <= 5 THEN 'Advanced' ELSE 'Expert' END AS `level`, CASE WHEN `ss`.`initial_experience` + timestampdiff(YEAR,`ss`.`created_at`,current_timestamp()) <= 1 THEN 40 WHEN `ss`.`initial_experience` + timestampdiff(YEAR,`ss`.`created_at`,current_timestamp()) <= 3 THEN 60 WHEN `ss`.`initial_experience` + timestampdiff(YEAR,`ss`.`created_at`,current_timestamp()) <= 5 THEN 80 ELSE 95 END AS `percentage` FROM ((`student_skills` `ss` join `master_skills` `ms` on(`ss`.`skill_id` = `ms`.`skill_id`)) left join `skill_categories` `sc` on(`ms`.`category_id` = `sc`.`category_id`)) ;

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
  ADD KEY `Email` (`Email`),
  ADD KEY `idx_cert_verified_by` (`verified_by`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`Email`);

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
-- Indexes for table `degrees`
--
ALTER TABLE `degrees`
  ADD PRIMARY KEY (`degree_id`),
  ADD UNIQUE KEY `uq_degree_domain` (`domain`,`degree_name`),
  ADD KEY `idx_degrees_domain` (`domain`);

--
-- Indexes for table `feedback_improvements`
--
ALTER TABLE `feedback_improvements`
  ADD PRIMARY KEY (`feedback_id`,`area`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_internships_company_email` (`company_email`),
  ADD KEY `fk_internships_industry` (`industry_id`);

--
-- Indexes for table `internship_applications`
--
ALTER TABLE `internship_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `Email` (`Email`),
  ADD KEY `internship_id` (`internship_id`);

--
-- Indexes for table `internship_industries`
--
ALTER TABLE `internship_industries`
  ADD PRIMARY KEY (`industry_id`),
  ADD UNIQUE KEY `uq_internship_industry_name` (`industry_name`);

--
-- Indexes for table `internship_interviews`
--
ALTER TABLE `internship_interviews`
  ADD PRIMARY KEY (`interview_id`),
  ADD KEY `idx_internship_interviews_application` (`application_id`);

--
-- Indexes for table `internship_moderation_logs`
--
ALTER TABLE `internship_moderation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_iml_internship` (`internship_id`),
  ADD KEY `idx_iml_admin` (`admin_email`);

--
-- Indexes for table `internship_tech_tags`
--
ALTER TABLE `internship_tech_tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `uq_internship_tag_name` (`tag_name`);

--
-- Indexes for table `internship_tech_tag_map`
--
ALTER TABLE `internship_tech_tag_map`
  ADD PRIMARY KEY (`internship_id`,`tag_id`),
  ADD KEY `idx_internship_tag_map_tag` (`tag_id`);

--
-- Indexes for table `master_skills`
--
ALTER TABLE `master_skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD UNIQUE KEY `uq_master_skill_name` (`skill_name`),
  ADD KEY `idx_master_skills_category` (`category_id`);

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
  ADD PRIMARY KEY (`Email`);

--
-- Indexes for table `org_teams`
--
ALTER TABLE `org_teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_project_team_name` (`project_id`,`name`),
  ADD KEY `idx_org_teams_leader` (`leader_email`);

--
-- Indexes for table `org_team_members`
--
ALTER TABLE `org_team_members`
  ADD PRIMARY KEY (`team_id`,`Email`),
  ADD KEY `fk_otm_student` (`Email`);

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
  ADD KEY `fk_projects_org` (`organization_email`),
  ADD KEY `fk_projects_category` (`category_id`);

--
-- Indexes for table `project_applications`
--
ALTER TABLE `project_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_project_student` (`project_id`,`Email`),
  ADD KEY `idx_pa_email` (`Email`);

--
-- Indexes for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `uq_category_name` (`category_name`);

--
-- Indexes for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_documents_project` (`project_id`);

--
-- Indexes for table `project_feedback`
--
ALTER TABLE `project_feedback`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_feedback_team` (`team_id`);

--
-- Indexes for table `project_moderation_logs`
--
ALTER TABLE `project_moderation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pml_project` (`project_id`),
  ADD KEY `idx_pml_admin` (`admin_email`);

--
-- Indexes for table `project_proposals`
--
ALTER TABLE `project_proposals`
  ADD PRIMARY KEY (`proposal_id`),
  ADD UNIQUE KEY `uq_student_project_proposal` (`project_id`,`student_email`),
  ADD KEY `idx_prop_student` (`student_email`);

--
-- Indexes for table `project_skills`
--
ALTER TABLE `project_skills`
  ADD PRIMARY KEY (`project_id`,`skill_name`);

--
-- Indexes for table `proposal_skills`
--
ALTER TABLE `proposal_skills`
  ADD PRIMARY KEY (`proposal_id`,`skill_name`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `skill_categories`
--
ALTER TABLE `skill_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `uq_skill_category_name` (`category_name`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`Email`),
  ADD KEY `idx_student_domain` (`domain`),
  ADD KEY `idx_student_degree` (`degree_id`);

--
-- Indexes for table `student_internal_portfolio_overrides`
--
ALTER TABLE `student_internal_portfolio_overrides`
  ADD PRIMARY KEY (`student_project_id`),
  ADD KEY `idx_sipo_email` (`student_email`);

--
-- Indexes for table `student_portfolio_projects`
--
ALTER TABLE `student_portfolio_projects`
  ADD PRIMARY KEY (`portfolio_project_id`),
  ADD UNIQUE KEY `uq_spp_legacy` (`legacy_portfolio_id`),
  ADD KEY `idx_spp_owner_date` (`student_email`,`created_at`);

--
-- Indexes for table `student_portfolio_project_technologies`
--
ALTER TABLE `student_portfolio_project_technologies`
  ADD PRIMARY KEY (`portfolio_project_id`,`technology_id`),
  ADD KEY `idx_sppt_technology` (`technology_id`);

--
-- Indexes for table `student_profile_views`
--
ALTER TABLE `student_profile_views`
  ADD PRIMARY KEY (`view_id`),
  ADD KEY `idx_spv_student` (`student_email`);

--
-- Indexes for table `student_projects`
--
ALTER TABLE `student_projects`
  ADD PRIMARY KEY (`student_project_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `student_resumes`
--
ALTER TABLE `student_resumes`
  ADD PRIMARY KEY (`resume_id`),
  ADD KEY `idx_resume_student` (`student_email`);

--
-- Indexes for table `student_resume_items`
--
ALTER TABLE `student_resume_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_resume_item` (`resume_id`,`item_type`,`item_reference_id`);

--
-- Indexes for table `student_saved_internships`
--
ALTER TABLE `student_saved_internships`
  ADD PRIMARY KEY (`student_email`,`internship_id`),
  ADD KEY `idx_ssi_internship` (`internship_id`);

--
-- Indexes for table `student_saved_projects`
--
ALTER TABLE `student_saved_projects`
  ADD PRIMARY KEY (`student_email`,`project_id`),
  ADD KEY `idx_ssp_project` (`project_id`);

--
-- Indexes for table `student_skills`
--
ALTER TABLE `student_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_skill` (`Email`,`skill_id`),
  ADD KEY `idx_ss_skill` (`skill_id`);

--
-- Indexes for table `student_technologies`
--
ALTER TABLE `student_technologies`
  ADD PRIMARY KEY (`technology_id`),
  ADD UNIQUE KEY `uq_technology_name` (`technology_name`);

--
-- Indexes for table `team_deadline_changes`
--
ALTER TABLE `team_deadline_changes`
  ADD PRIMARY KEY (`change_id`),
  ADD KEY `fk_tdc_team` (`team_id`);

--
-- Indexes for table `team_files`
--
ALTER TABLE `team_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `fk_tf_team` (`team_id`),
  ADD KEY `fk_tf_user` (`uploaded_by`);

--
-- Indexes for table `team_final_reports`
--
ALTER TABLE `team_final_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `uq_team_final_report` (`team_id`),
  ADD KEY `fk_tfr_student` (`submitted_by`);

--
-- Indexes for table `team_messages`
--
ALTER TABLE `team_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `fk_tmmsg_team` (`team_id`),
  ADD KEY `fk_tmmsg_user` (`sender_email`);

--
-- Indexes for table `team_tasks`
--
ALTER TABLE `team_tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `fk_tt_team` (`team_id`),
  ADD KEY `fk_tt_student` (`assigned_to`);

--
-- Indexes for table `universityemails`
--
ALTER TABLE `universityemails`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `university_faculties`
--
ALTER TABLE `university_faculties`
  ADD PRIMARY KEY (`domain`);

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
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `complain`
--
ALTER TABLE `complain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `degrees`
--
ALTER TABLE `degrees`
  MODIFY `degree_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT for table `internship_industries`
--
ALTER TABLE `internship_industries`
  MODIFY `industry_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `internship_interviews`
--
ALTER TABLE `internship_interviews`
  MODIFY `interview_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `internship_moderation_logs`
--
ALTER TABLE `internship_moderation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `internship_tech_tags`
--
ALTER TABLE `internship_tech_tags`
  MODIFY `tag_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `master_skills`
--
ALTER TABLE `master_skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `org_teams`
--
ALTER TABLE `org_teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `portfolio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `project_applications`
--
ALTER TABLE `project_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_feedback`
--
ALTER TABLE `project_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_moderation_logs`
--
ALTER TABLE `project_moderation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_proposals`
--
ALTER TABLE `project_proposals`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `skill_categories`
--
ALTER TABLE `skill_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_portfolio_projects`
--
ALTER TABLE `student_portfolio_projects`
  MODIFY `portfolio_project_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_profile_views`
--
ALTER TABLE `student_profile_views`
  MODIFY `view_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `student_projects`
--
ALTER TABLE `student_projects`
  MODIFY `student_project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `student_resumes`
--
ALTER TABLE `student_resumes`
  MODIFY `resume_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_resume_items`
--
ALTER TABLE `student_resume_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `student_skills`
--
ALTER TABLE `student_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `student_technologies`
--
ALTER TABLE `student_technologies`
  MODIFY `technology_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_deadline_changes`
--
ALTER TABLE `team_deadline_changes`
  MODIFY `change_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_files`
--
ALTER TABLE `team_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_final_reports`
--
ALTER TABLE `team_final_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_messages`
--
ALTER TABLE `team_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_tasks`
--
ALTER TABLE `team_tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `admin` (`Email`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `company`
--
ALTER TABLE `company`
  ADD CONSTRAINT `company_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `complain`
--
ALTER TABLE `complain`
  ADD CONSTRAINT `complain_ibfk_1` FOREIGN KEY (`email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `degrees`
--
ALTER TABLE `degrees`
  ADD CONSTRAINT `fk_degrees_faculty` FOREIGN KEY (`domain`) REFERENCES `university_faculties` (`domain`) ON UPDATE CASCADE;

--
-- Constraints for table `feedback_improvements`
--
ALTER TABLE `feedback_improvements`
  ADD CONSTRAINT `fk_fi_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `project_feedback` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `internships`
--
ALTER TABLE `internships`
  ADD CONSTRAINT `fk_internships_company_email` FOREIGN KEY (`company_email`) REFERENCES `company` (`Email`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_internships_industry` FOREIGN KEY (`industry_id`) REFERENCES `internship_industries` (`industry_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `internship_applications`
--
ALTER TABLE `internship_applications`
  ADD CONSTRAINT `internship_applications_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `internship_applications_ibfk_2` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `internship_interviews`
--
ALTER TABLE `internship_interviews`
  ADD CONSTRAINT `fk_internship_interviews_application` FOREIGN KEY (`application_id`) REFERENCES `internship_applications` (`application_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `internship_moderation_logs`
--
ALTER TABLE `internship_moderation_logs`
  ADD CONSTRAINT `fk_iml_admin` FOREIGN KEY (`admin_email`) REFERENCES `admin` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_iml_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `internship_tech_tag_map`
--
ALTER TABLE `internship_tech_tag_map`
  ADD CONSTRAINT `fk_internship_tag_map_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_internship_tag_map_tag` FOREIGN KEY (`tag_id`) REFERENCES `internship_tech_tags` (`tag_id`) ON UPDATE CASCADE;

--
-- Constraints for table `master_skills`
--
ALTER TABLE `master_skills`
  ADD CONSTRAINT `fk_ms_category` FOREIGN KEY (`category_id`) REFERENCES `skill_categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `organization`
--
ALTER TABLE `organization`
  ADD CONSTRAINT `organization_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `org_teams`
--
ALTER TABLE `org_teams`
  ADD CONSTRAINT `fk_org_teams_leader` FOREIGN KEY (`leader_email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_org_teams_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `org_team_members`
--
ALTER TABLE `org_team_members`
  ADD CONSTRAINT `fk_otm_student` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_otm_team` FOREIGN KEY (`team_id`) REFERENCES `org_teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD CONSTRAINT `portfolio_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_category` FOREIGN KEY (`category_id`) REFERENCES `project_categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_projects_org` FOREIGN KEY (`organization_email`) REFERENCES `organization` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `project_applications`
--
ALTER TABLE `project_applications`
  ADD CONSTRAINT `fk_pa_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pa_student` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD CONSTRAINT `fk_project_documents_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_feedback`
--
ALTER TABLE `project_feedback`
  ADD CONSTRAINT `fk_pf_team` FOREIGN KEY (`team_id`) REFERENCES `org_teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_moderation_logs`
--
ALTER TABLE `project_moderation_logs`
  ADD CONSTRAINT `fk_pml_admin` FOREIGN KEY (`admin_email`) REFERENCES `admin` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pml_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_proposals`
--
ALTER TABLE `project_proposals`
  ADD CONSTRAINT `fk_prop_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prop_student` FOREIGN KEY (`student_email`) REFERENCES `student` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_skills`
--
ALTER TABLE `project_skills`
  ADD CONSTRAINT `fk_ps_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proposal_skills`
--
ALTER TABLE `proposal_skills`
  ADD CONSTRAINT `fk_propskills_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `project_proposals` (`proposal_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_student_degree` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`degree_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_domain` FOREIGN KEY (`domain`) REFERENCES `university_faculties` (`domain`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `user` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_internal_portfolio_overrides`
--
ALTER TABLE `student_internal_portfolio_overrides`
  ADD CONSTRAINT `fk_sipo_assignment` FOREIGN KEY (`student_project_id`) REFERENCES `student_projects` (`student_project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sipo_student` FOREIGN KEY (`student_email`) REFERENCES `student` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `student_portfolio_projects`
--
ALTER TABLE `student_portfolio_projects`
  ADD CONSTRAINT `fk_spp_student` FOREIGN KEY (`student_email`) REFERENCES `student` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `student_portfolio_project_technologies`
--
ALTER TABLE `student_portfolio_project_technologies`
  ADD CONSTRAINT `fk_sppt_project` FOREIGN KEY (`portfolio_project_id`) REFERENCES `student_portfolio_projects` (`portfolio_project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sppt_technology` FOREIGN KEY (`technology_id`) REFERENCES `student_technologies` (`technology_id`);

--
-- Constraints for table `student_profile_views`
--
ALTER TABLE `student_profile_views`
  ADD CONSTRAINT `fk_spv_student` FOREIGN KEY (`student_email`) REFERENCES `student` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `student_projects`
--
ALTER TABLE `student_projects`
  ADD CONSTRAINT `student_projects_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_projects_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_resumes`
--
ALTER TABLE `student_resumes`
  ADD CONSTRAINT `fk_resume_student` FOREIGN KEY (`student_email`) REFERENCES `student` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_resume_items`
--
ALTER TABLE `student_resume_items`
  ADD CONSTRAINT `fk_sri_resume` FOREIGN KEY (`resume_id`) REFERENCES `student_resumes` (`resume_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_saved_internships`
--
ALTER TABLE `student_saved_internships`
  ADD CONSTRAINT `fk_ssi_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ssi_student` FOREIGN KEY (`student_email`) REFERENCES `student` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_saved_projects`
--
ALTER TABLE `student_saved_projects`
  ADD CONSTRAINT `fk_ssp_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ssp_student` FOREIGN KEY (`student_email`) REFERENCES `student` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_skills`
--
ALTER TABLE `student_skills`
  ADD CONSTRAINT `fk_ss_skill` FOREIGN KEY (`skill_id`) REFERENCES `master_skills` (`skill_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ss_student` FOREIGN KEY (`Email`) REFERENCES `student` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `team_deadline_changes`
--
ALTER TABLE `team_deadline_changes`
  ADD CONSTRAINT `fk_tdc_team` FOREIGN KEY (`team_id`) REFERENCES `org_teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_files`
--
ALTER TABLE `team_files`
  ADD CONSTRAINT `fk_team_files_team` FOREIGN KEY (`team_id`) REFERENCES `org_teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_team_files_user` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `team_final_reports`
--
ALTER TABLE `team_final_reports`
  ADD CONSTRAINT `fk_tfr_student` FOREIGN KEY (`submitted_by`) REFERENCES `student` (`Email`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tfr_team` FOREIGN KEY (`team_id`) REFERENCES `org_teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_messages`
--
ALTER TABLE `team_messages`
  ADD CONSTRAINT `fk_tmmsg_team` FOREIGN KEY (`team_id`) REFERENCES `org_teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tmmsg_user` FOREIGN KEY (`sender_email`) REFERENCES `user` (`Email`) ON DELETE CASCADE;

--
-- Constraints for table `team_tasks`
--
ALTER TABLE `team_tasks`
  ADD CONSTRAINT `fk_tt_student` FOREIGN KEY (`assigned_to`) REFERENCES `student` (`Email`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tt_team` FOREIGN KEY (`team_id`) REFERENCES `org_teams` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
