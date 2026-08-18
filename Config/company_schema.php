<?php

date_default_timezone_set('Asia/Kolkata');

function company_db_has_column($conn, $table, $column)
{
    $result = $conn->query("SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'");
    return ($result && $result->num_rows > 0);
}

function ensure_company_schema($conn)
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS companies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            company_name VARCHAR(255) NOT NULL DEFAULT 'My Company',
            industry_sector VARCHAR(255) DEFAULT 'Technology',
            contact_person VARCHAR(255) DEFAULT '',
            contact_number VARCHAR(50) DEFAULT '',
            website VARCHAR(255) DEFAULT '',
            location VARCHAR(255) DEFAULT '',
            company_size VARCHAR(100) DEFAULT '51-200',
            description TEXT DEFAULT '',
            logo VARCHAR(255) DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY user_id_unique (user_id)
        )
    ");

    if (!company_db_has_column($conn, 'companies', 'user_id')) {
        $conn->query("ALTER TABLE companies ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER id");
    }

    $conn->query("
        CREATE TABLE IF NOT EXISTS internships (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            location VARCHAR(255) DEFAULT 'Remote',
            duration VARCHAR(100) DEFAULT '3 months',
            type VARCHAR(100) DEFAULT 'Full-time',
            status VARCHAR(50) DEFAULT 'active',
            deadline DATE DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY company_idx (company_id)
        )
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_id INT NOT NULL,
            applicant_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            position VARCHAR(255) NOT NULL,
            status VARCHAR(50) DEFAULT 'new',
            applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY company_idx (company_id),
            KEY status_idx (status)
        )
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS interviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_id INT NOT NULL,
            applicant_name VARCHAR(255) NOT NULL,
            position VARCHAR(255) NOT NULL,
            interview_date DATETIME NOT NULL,
            interview_type VARCHAR(100) DEFAULT 'Video Call',
            status VARCHAR(50) DEFAULT 'scheduled',
            notes TEXT DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY company_idx (company_id)
        )
    ");
}

function ensure_company_record($conn, $user)
{
    if (empty($user)) {
        return null;
    }

    $userId = isset($user['id']) ? (int) $user['id'] : 0;
    if ($userId <= 0) {
        return null;
    }

    $check = $conn->query("SELECT * FROM companies WHERE user_id = " . $userId . " LIMIT 1");
    if ($check && $check->num_rows > 0) {
        return $check->fetch_assoc();
    }

    $defaultName = isset($user['username']) && $user['username'] !== '' ? $user['username'] : 'My Company';
    $stmt = $conn->prepare("INSERT INTO companies (user_id, company_name, location, contact_person, company_size, description) VALUES (?, ?, 'Colombo, Sri Lanka', ?, '51-200', 'We are building great products and mentoring future talent.')");
    $contactPerson = isset($user['username']) ? $user['username'] : 'Company Manager';
    $stmt->bind_param("iss", $userId, $defaultName, $contactPerson);
    $stmt->execute();

    $newCompany = $conn->query("SELECT * FROM companies WHERE user_id = " . $userId . " LIMIT 1");
    return $newCompany ? $newCompany->fetch_assoc() : null;
}

function seed_company_demo_data($conn, $companyId)
{
    if ($companyId <= 0) {
        return;
    }

    $internshipsCheck = $conn->query("SELECT id FROM internships WHERE company_id = " . (int) $companyId . " LIMIT 1");
    if (!$internshipsCheck || $internshipsCheck->num_rows === 0) {
        $internships = array(
            array('Full Stack Developer Intern', 'Build and maintain responsive web applications using PHP, JavaScript, and MySQL.', 'Remote', '6 months', 'Full-time', 'active', '2026-09-15'),
            array('Data Science Intern', 'Analyze product data and build dashboards to inform business decisions.', 'Hybrid', '3 months', 'Part-time', 'active', '2026-09-25'),
            array('Product Design Intern', 'Design intuitive workflows and polished user experiences for our platform.', 'On-site', '4 months', 'Full-time', 'active', '2026-10-05')
        );

        foreach ($internships as $internship) {
            $stmt = $conn->prepare("INSERT INTO internships (company_id, title, description, location, duration, type, status, deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $companyId, $internship[0], $internship[1], $internship[2], $internship[3], $internship[4], $internship[5], $internship[6]);
            $stmt->execute();
        }
    }

    $applicationsCheck = $conn->query("SELECT id FROM applications WHERE company_id = " . (int) $companyId . " LIMIT 1");
    if (!$applicationsCheck || $applicationsCheck->num_rows === 0) {
        $applications = array(
            array('Aisha Perera', 'aisha@university.edu', 'Full Stack Developer Intern', 'new'),
            array('Nimal Jayasinghe', 'nimal@university.edu', 'Data Science Intern', 'shortlisted'),
            array('Tharindu Silva', 'tharindu@university.edu', 'Product Design Intern', 'interview')
        );

        foreach ($applications as $app) {
            $stmt = $conn->prepare("INSERT INTO applications (company_id, applicant_name, email, position, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $companyId, $app[0], $app[1], $app[2], $app[3]);
            $stmt->execute();
        }
    }

    $interviewsCheck = $conn->query("SELECT id FROM interviews WHERE company_id = " . (int) $companyId . " LIMIT 1");
    if (!$interviewsCheck || $interviewsCheck->num_rows === 0) {
        $interviews = array(
            array('Nimal Jayasinghe', 'Data Science Intern', '2026-08-25 10:00:00', 'Video Call', 'scheduled', 'Portfolio review and technical discussion'),
            array('Tharindu Silva', 'Product Design Intern', '2026-08-27 14:30:00', 'In-person', 'scheduled', 'Design challenge review')
        );

        foreach ($interviews as $interview) {
            $stmt = $conn->prepare("INSERT INTO interviews (company_id, applicant_name, position, interview_date, interview_type, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $companyId, $interview[0], $interview[1], $interview[2], $interview[3], $interview[4], $interview[5]);
            $stmt->execute();
        }
    }
}

?>
