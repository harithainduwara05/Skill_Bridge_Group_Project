<?php
declare(strict_types=1);

final class StudentInternshipRepository
{
    private mysqli $db;
    private array $columns = [];
    private array $tables = [];

    public function __construct(mysqli $db)
    {
        $this->db = $db;
        $this->ensureSavedInternshipsTable();
    }

    private function ensureSavedInternshipsTable(): void
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS student_saved_internships (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_email VARCHAR(100) NOT NULL,
                internship_id INT NOT NULL,
                saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_student_saved_internship (student_email, internship_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->tables['student_saved_internships'] = true;
        } catch (Throwable $e) {
            error_log('StudentInternshipRepository ensureSavedInternshipsTable: ' . $e->getMessage());
        }
    }

    private function hasTable(string $table): bool
    {
        if (!array_key_exists($table, $this->tables)) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
            );
            $stmt->bind_param('s', $table);
            $stmt->execute();
            $this->tables[$table] = (int) $stmt->get_result()->fetch_row()[0] > 0;
            $stmt->close();
        }
        return $this->tables[$table];
    }

    private function hasColumn(string $table, string $column): bool
    {
        $key = "{$table}.{$column}";
        if (!array_key_exists($key, $this->columns)) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $stmt->bind_param('ss', $table, $column);
            $stmt->execute();
            $this->columns[$key] = (int) $stmt->get_result()->fetch_row()[0] > 0;
            $stmt->close();
        }
        return $this->columns[$key];
    }

    private function query(string $sql, string $types = '', array $params = []): mysqli_result|bool
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare internship query.');
        }
        try {
            if ($types !== '') {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            return $res === false ? true : $res;
        } finally {
            $stmt->close();
        }
    }

    private function moderationFilterSql(): string
    {
        if ($this->hasTable('internship_moderation_logs')) {
            return "COALESCE((SELECT ml.status FROM internship_moderation_logs ml WHERE ml.internship_id = i.id ORDER BY ml.id DESC LIMIT 1), 'Active') NOT IN ('Suspended', 'Terminated')";
        }
        if ($this->hasColumn('internships', 'status')) {
            return "LOWER(COALESCE(i.status, 'active')) NOT IN ('suspended', 'terminated')";
        }
        return '1 = 1';
    }

    private function selectClause(): string
    {
        $companyNameExpr = $this->hasColumn('internships', 'company')
            ? "COALESCE(NULLIF(TRIM(i.company), ''), c.Name, i.company_email)"
            : "COALESCE(c.Name, i.company_email)";

        $industryExpr = ($this->hasColumn('internships', 'industry_id') && $this->hasTable('internship_industries'))
            ? ($this->hasColumn('internships', 'industry')
                ? "COALESCE(NULLIF(TRIM(i.industry), ''), ind.industry_name, 'General')"
                : "COALESCE(ind.industry_name, 'General')")
            : ($this->hasColumn('internships', 'industry')
                ? "COALESCE(NULLIF(TRIM(i.industry), ''), 'General')"
                : "'General'");

        $industryJoin = ($this->hasColumn('internships', 'industry_id') && $this->hasTable('internship_industries'))
            ? 'LEFT JOIN internship_industries ind ON ind.industry_id = i.industry_id'
            : '';

        $durationExpr = $this->hasColumn('internships', 'duration_value')
            ? "CONCAT(COALESCE(i.duration_value, 0), ' ', COALESCE(i.duration_unit, 'month'), IF(COALESCE(i.duration_value, 0) = 1, '', 's'))"
            : ($this->hasColumn('internships', 'duration') ? "COALESCE(i.duration, 'Not specified')" : "'Not specified'");

        $deadlineCol = $this->hasColumn('internships', 'deadline_date')
            ? 'i.deadline_date'
            : ($this->hasColumn('internships', 'deadline') ? 'i.deadline' : 'NULL');

        $tagsExpr = ($this->hasTable('internship_tech_tag_map') && $this->hasTable('internship_tech_tags'))
            ? ($this->hasColumn('internships', 'tech_tags')
                ? "COALESCE(NULLIF(TRIM(i.tech_tags), ''), (SELECT GROUP_CONCAT(DISTINCT t.tag_name ORDER BY t.tag_name SEPARATOR ', ') FROM internship_tech_tag_map m INNER JOIN internship_tech_tags t ON t.tag_id = m.tag_id WHERE m.internship_id = i.id))"
                : "(SELECT GROUP_CONCAT(DISTINCT t.tag_name ORDER BY t.tag_name SEPARATOR ', ') FROM internship_tech_tag_map m INNER JOIN internship_tech_tags t ON t.tag_id = m.tag_id WHERE m.internship_id = i.id)")
            : ($this->hasColumn('internships', 'tech_tags') ? 'i.tech_tags' : "''");

        $modStatusExpr = $this->hasTable('internship_moderation_logs')
            ? "COALESCE((SELECT ml.status FROM internship_moderation_logs ml WHERE ml.internship_id = i.id ORDER BY ml.id DESC LIMIT 1), 'Active')"
            : ($this->hasColumn('internships', 'status') ? "COALESCE(i.status, 'Active')" : "'Active'");

        return "SELECT
                    i.*,
                    {$companyNameExpr} AS company_name,
                    c.location AS company_hq_location,
                    c.website AS company_website,
                    c.Status AS company_verified_status,
                    {$industryExpr} AS industry_name,
                    {$durationExpr} AS duration_label,
                    {$deadlineCol} AS raw_deadline,
                    {$tagsExpr} AS skills_csv,
                    {$modStatusExpr} AS moderation_status
                FROM internships i
                LEFT JOIN company c ON LOWER(TRIM(c.Email)) = LOWER(TRIM(i.company_email))
                {$industryJoin}";
    }

    public function industries(): array
    {
        $list = [];
        if ($this->hasTable('internship_industries')) {
            $rows = $this->query('SELECT DISTINCT industry_name FROM internship_industries WHERE industry_name <> "" ORDER BY industry_name ASC')->fetch_all(MYSQLI_ASSOC);
            foreach ($rows as $r) {
                $list[] = (string) $r['industry_name'];
            }
        }
        if ($this->hasColumn('internships', 'industry')) {
            $rows = $this->query('SELECT DISTINCT industry FROM internships WHERE industry IS NOT NULL AND TRIM(industry) <> "" ORDER BY industry ASC')->fetch_all(MYSQLI_ASSOC);
            foreach ($rows as $r) {
                $val = trim((string) $r['industry']);
                if ($val !== '' && !in_array($val, $list, true)) {
                    $list[] = $val;
                }
            }
        }
        return $list;
    }

    public function listInternships(array $filters = []): array
    {
        $where = [$this->moderationFilterSql()];
        $types = '';
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            if ($this->hasColumn('internships', 'company')) {
                $where[] = '(i.title LIKE ? OR i.description LIKE ? OR i.company LIKE ? OR c.Name LIKE ? OR i.location LIKE ?)';
                array_push($params, $like, $like, $like, $like, $like);
                $types .= 'sssss';
            } else {
                $where[] = '(i.title LIKE ? OR i.description LIKE ? OR c.Name LIKE ? OR i.location LIKE ?)';
                array_push($params, $like, $like, $like, $like);
                $types .= 'ssss';
            }
        }

        $industry = trim((string) ($filters['industry'] ?? ''));
        if ($industry !== '') {
            if ($this->hasColumn('internships', 'industry_id') && $this->hasTable('internship_industries')) {
                if ($this->hasColumn('internships', 'industry')) {
                    $where[] = '(ind.industry_name = ? OR i.industry = ?)';
                    array_push($params, $industry, $industry);
                    $types .= 'ss';
                } else {
                    $where[] = 'ind.industry_name = ?';
                    $params[] = $industry;
                    $types .= 's';
                }
            } elseif ($this->hasColumn('internships', 'industry')) {
                $where[] = 'i.industry = ?';
                $params[] = $industry;
                $types .= 's';
            }
        }

        $workMode = trim((string) ($filters['work_mode'] ?? ''));
        if ($workMode !== '' && $this->hasColumn('internships', 'work_mode')) {
            $where[] = '(LOWER(COALESCE(i.work_mode, "")) = LOWER(?) OR LOWER(COALESCE(i.location, "")) LIKE LOWER(?))';
            $params[] = $workMode;
            $params[] = '%' . $workMode . '%';
            $types .= 'ss';
        }

        $schedule = trim((string) ($filters['schedule'] ?? ''));
        if ($schedule !== '' && $this->hasColumn('internships', 'internship_type')) {
            $where[] = 'LOWER(COALESCE(i.internship_type, "")) = LOWER(?)';
            $params[] = $schedule;
            $types .= 's';
        }

        $sql = $this->selectClause() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY i.id DESC';
        $rows = $this->query($sql, $types, $params)->fetch_all(MYSQLI_ASSOC);

        foreach ($rows as &$row) {
            $row['skills'] = array_values(array_filter(array_map('trim', explode(',', (string) ($row['skills_csv'] ?? '')))));
            $row['formatted_deadline'] = !empty($row['raw_deadline']) && strtotime((string) $row['raw_deadline']) !== false
                ? date('M d, Y', strtotime((string) $row['raw_deadline']))
                : 'Open / Rolling';
            $row['is_expired'] = !empty($row['raw_deadline']) && strtotime((string) $row['raw_deadline']) !== false
                && strtotime((string) $row['raw_deadline']) < strtotime('today');
        }
        unset($row);

        return $rows;
    }

    public function findInternship(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $sql = $this->selectClause() . ' WHERE i.id = ? AND ' . $this->moderationFilterSql() . ' LIMIT 1';
        $row = $this->query($sql, 'i', [$id])->fetch_assoc();
        if (!$row) {
            return null;
        }
        $row['skills'] = array_values(array_filter(array_map('trim', explode(',', (string) ($row['skills_csv'] ?? '')))));
        $row['formatted_deadline'] = !empty($row['raw_deadline']) && strtotime((string) $row['raw_deadline']) !== false
            ? date('M d, Y', strtotime((string) $row['raw_deadline']))
            : 'Open / Rolling';
        $row['is_expired'] = !empty($row['raw_deadline']) && strtotime((string) $row['raw_deadline']) !== false
            && strtotime((string) $row['raw_deadline']) < strtotime('today');
        return $row;
    }

    public function savedIds(string $email): array
    {
        if (!$this->hasTable('student_saved_internships')) {
            return [];
        }
        $rows = $this->query(
            'SELECT internship_id FROM student_saved_internships WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?))',
            's',
            [$email]
        )->fetch_all(MYSQLI_ASSOC);
        return array_map(static fn($r) => (int) $r['internship_id'], $rows);
    }

    public function savedInternships(string $email): array
    {
        if (!$this->hasTable('student_saved_internships')) {
            return [];
        }
        $sql = $this->selectClause() . '
            INNER JOIN student_saved_internships ssi ON ssi.internship_id = i.id
            WHERE LOWER(TRIM(ssi.student_email)) = LOWER(TRIM(?))
              AND ' . $this->moderationFilterSql() . '
            ORDER BY ssi.saved_at DESC';
        $rows = $this->query($sql, 's', [$email])->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as &$row) {
            $row['skills'] = array_values(array_filter(array_map('trim', explode(',', (string) ($row['skills_csv'] ?? '')))));
            $row['formatted_deadline'] = !empty($row['raw_deadline']) && strtotime((string) $row['raw_deadline']) !== false
                ? date('M d, Y', strtotime((string) $row['raw_deadline']))
                : 'Open / Rolling';
        }
        unset($row);
        return $rows;
    }

    public function toggleSaved(string $email, int $internshipId): bool
    {
        $this->ensureSavedInternshipsTable();
        if (!$this->findInternship($internshipId)) {
            throw new InvalidArgumentException('Internship not found or unavailable.');
        }

        $exists = (int) $this->query(
            'SELECT COUNT(*) FROM student_saved_internships WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?)) AND internship_id = ?',
            'si',
            [$email, $internshipId]
        )->fetch_row()[0];

        if ($exists > 0) {
            $this->query(
                'DELETE FROM student_saved_internships WHERE LOWER(TRIM(student_email)) = LOWER(TRIM(?)) AND internship_id = ?',
                'si',
                [$email, $internshipId]
            );
            return false;
        }

        $this->query(
            'INSERT INTO student_saved_internships (student_email, internship_id) VALUES (?, ?)',
            'si',
            [$email, $internshipId]
        );
        return true;
    }

    public function hasApplied(string $email, int $internshipId): bool
    {
        $count = (int) $this->query(
            'SELECT COUNT(*) FROM internship_applications WHERE LOWER(TRIM(Email)) = LOWER(TRIM(?)) AND internship_id = ?',
            'si',
            [$email, $internshipId]
        )->fetch_row()[0];
        return $count > 0;
    }

    public function appliedIds(string $email): array
    {
        $rows = $this->query(
            'SELECT DISTINCT internship_id FROM internship_applications WHERE LOWER(TRIM(Email)) = LOWER(TRIM(?))',
            's',
            [$email]
        )->fetch_all(MYSQLI_ASSOC);
        return array_map(static fn($r) => (int) $r['internship_id'], $rows);
    }

    public function submitApplication(string $email, int $internshipId, array $data): void
    {
        $internship = $this->findInternship($internshipId);
        if (!$internship) {
            throw new InvalidArgumentException('This internship is no longer available.');
        }
        if ($internship['is_expired']) {
            throw new InvalidArgumentException('The application deadline for this internship has passed.');
        }
        if ($this->hasApplied($email, $internshipId)) {
            throw new InvalidArgumentException('You have already submitted an application for this internship.');
        }

        $title = trim((string) ($data['application_title'] ?? ''));
        $coverNote = trim((string) ($data['cover_note'] ?? ''));
        $experience = trim((string) ($data['relevant_experience'] ?? ''));
        $rationale = trim((string) ($data['selection_rationale'] ?? ''));
        $resumeFile = $data['resume_file'] ?? null;

        if ($title === '' || $coverNote === '' || $experience === '' || $rationale === '') {
            throw new InvalidArgumentException('Please complete all required application fields.');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO internship_applications
                (Email, internship_id, application_title, cover_note, relevant_experience, selection_rationale, resume_file, status, applied_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', CURDATE())"
        );
        if (!$stmt) {
            throw new RuntimeException('Unable to prepare application submission.');
        }

        $stmt->bind_param(
            'sisssss',
            $email,
            $internshipId,
            $title,
            $coverNote,
            $experience,
            $rationale,
            $resumeFile
        );
        $stmt->execute();
        $stmt->close();

        // Notify student & company if notifications table exists
        if ($this->hasTable('notifications')) {
            $posTitle = (string) ($internship['title'] ?? 'Internship');
            $compName = (string) ($internship['company_name'] ?? 'Company');
            $stuMsg = "Your application for {$posTitle} at {$compName} has been submitted.";
            $n1 = $this->db->prepare("INSERT INTO notifications (Email, title, message, type, status, created_at) VALUES (?, 'Application Submitted', ?, 'application', 'Unread', NOW())");
            if ($n1) {
                $n1->bind_param('ss', $email, $stuMsg);
                $n1->execute();
                $n1->close();
            }

            $compEmail = trim((string) ($internship['company_email'] ?? ''));
            if ($compEmail !== '') {
                $compMsg = "{$email} applied for {$posTitle}.";
                $n2 = $this->db->prepare("INSERT INTO notifications (Email, title, message, type, status, created_at) VALUES (?, 'New Internship Application', ?, 'candidate', 'Unread', NOW())");
                if ($n2) {
                    $n2->bind_param('ss', $compEmail, $compMsg);
                    $n2->execute();
                    $n2->close();
                }
            }
        }
    }
}