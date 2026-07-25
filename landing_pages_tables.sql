-- SQL structure for landing pages
-- You can import this file into phpMyAdmin to create the tables needed for the new pages

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `icon` varchar(50) DEFAULT '📁',
  `tag` varchar(100) DEFAULT NULL,
  `tag_class` varchar(50) DEFAULT NULL,
  `tech_stack` varchar(255) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `members` int(11) DEFAULT 1,
  `deadline` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some dummy data for projects
INSERT INTO `projects` (`title`, `company`, `icon`, `tag`, `tag_class`, `tech_stack`, `duration`, `members`, `deadline`) VALUES
('AI Model Optimization', 'TechCorp Solutions', '📊', 'High Demand', 'high-demand', 'Python, TensorFlow, AWS', '3 Months', 4, 'Oct 20, 2023'),
('Cloud Migration UI/UX', 'EduConnect', '🎨', 'UI/UX', 'ui-ux', 'React, Figma, Tailwind', '2 Months', 2, 'Nov 15, 2023');

CREATE TABLE IF NOT EXISTS `internships` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some dummy data for internships
INSERT INTO `internships` (`title`, `company`, `industry`, `logo_text`, `logo_style`, `tech_tags`, `duration`, `deadline`) VALUES
('UI/UX Designer', 'TechFlow Solutions', 'IT Services', '', '', 'Figma, Adobe XD', '6 Months', 'Nov 15, 2023'),
('Backend Developer Intern', 'Global Retail', 'Software Development', 'GR', 'background: #dbeafe;', 'Node.js, PostgreSQL', '3 Months', 'Oct 20, 2023');
