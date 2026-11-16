<?php
final class StudentPortfolioRepository
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
        $this->ensureCertificatesSchema();
    }

    private function ensureCertificatesSchema(): void
    {
        try {
            $colsRes = $this->db->query("SHOW COLUMNS FROM `certificates`");
            if (!$colsRes) {
                return;
            }
            $existing = [];
            while ($row = $colsRes->fetch_assoc()) {
                $existing[strtolower($row['Field'])] = true;
            }

            if (!isset($existing['expiration_date'])) {
                $this->db->query("ALTER TABLE `certificates` ADD COLUMN `expiration_date` DATE DEFAULT NULL AFTER `issue_date`");
            }
            if (!isset($existing['credential_id'])) {
                $this->db->query("ALTER TABLE `certificates` ADD COLUMN `credential_id` VARCHAR(150) DEFAULT NULL AFTER `expiration_date`");
            }
            if (!isset($existing['credential_url'])) {
                $this->db->query("ALTER TABLE `certificates` ADD COLUMN `credential_url` VARCHAR(500) DEFAULT NULL AFTER `credential_id`");
            }
            if (!isset($existing['created_at'])) {
                $this->db->query("ALTER TABLE `certificates` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
            }
        } catch (Throwable $e) {
            error_log('ensureCertificatesSchema: ' . $e->getMessage());
        }
    }

    public function summary(string $email): array
    {
        $projects = $this->scalar('SELECT COUNT(*) FROM student_portfolio_projects WHERE student_email = ?', $email);
        $certificates = $this->scalar('SELECT COUNT(*) FROM certificates WHERE Email = ?', $email);
        $views = $this->scalar('SELECT COUNT(*) FROM student_profile_views WHERE student_email = ?', $email);
        return ['projects' => $projects, 'certificates' => $certificates, 'views' => $views];
    }

    private function scalar(string $query, string $email): int
    {
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $value = $stmt->get_result()->fetch_row()[0] ?? 0;
        $stmt->close();
        return (int) $value;
    }

    public function projects(string $email, int $page = 1, int $perPage = 6): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare('SELECT portfolio_project_id, title, description, repository_url, project_image,
                 project_status, start_date, end_date, created_at
                 FROM student_portfolio_projects WHERE student_email = ?
                 ORDER BY created_at DESC, portfolio_project_id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('sii', $email, $perPage, $offset);
        $stmt->execute();
        $projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($projects as &$project) {
            $project['technologies'] = $this->technologies((int) $project['portfolio_project_id']);
        }
        unset($project);
        return $projects;
    }

    private function technologies(int $projectId): array
    {
        $stmt = $this->db->prepare('SELECT t.technology_name FROM student_technologies t
             JOIN student_portfolio_project_technologies pt ON pt.technology_id = t.technology_id
             WHERE pt.portfolio_project_id = ? ORDER BY t.technology_name');
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $names = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'technology_name');
        $stmt->close();
        return $names;
    }

    private function replaceTechnologies(int $projectId, array $technologies): void
    {
        $delete = $this->db->prepare('DELETE FROM student_portfolio_project_technologies WHERE portfolio_project_id = ?');
        $delete->bind_param('i', $projectId);
        $delete->execute();
        $delete->close();

        $insert = $this->db->prepare('INSERT IGNORE INTO student_technologies (technology_name) VALUES (?)');
        $lookup = $this->db->prepare('SELECT technology_id FROM student_technologies WHERE technology_name = ? LIMIT 1');
        $link = $this->db->prepare('INSERT INTO student_portfolio_project_technologies (portfolio_project_id, technology_id) VALUES (?, ?)');
        $seen = [];

        try {
            foreach ($technologies as $technology) {
                if (!is_string($technology)) {
                    continue;
                }
                $name = trim($technology);
                if ($name === '' || strlen($name) > 100) {
                    continue;
                }
                $key = strtolower($name);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                $insert->bind_param('s', $name);
                $insert->execute();
                $lookup->bind_param('s', $name);
                $lookup->execute();
                $technologyId = $lookup->get_result()->fetch_column();
                if (!$technologyId) {
                    throw new RuntimeException('Could not resolve portfolio technology.');
                }
                $technologyId = (int) $technologyId;
                $link->bind_param('ii', $projectId, $technologyId);
                $link->execute();
            }
        } finally {
            $insert->close();
            $lookup->close();
            $link->close();
        }
    }

    public function saveProject(string $email, array $data, ?int $id = null): int
    {
        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $url = trim((string) ($data['repository_url'] ?? ''));
        $status = trim((string) ($data['project_status'] ?? 'Currently Working'));
        $start = trim((string) ($data['start_date'] ?? '')) ?: null;
        $end = trim((string) ($data['end_date'] ?? '')) ?: null;
        $image = trim((string) ($data['project_image'] ?? '')) ?: null;
        $technologyNames = $data['technologies'] ?? [];
        
        // Strict Data Limits
        if (strlen($title) < 2 || strlen($title) > 150 || strlen($description) > 10000) {
            throw new InvalidArgumentException('Enter a valid project title (2–150 characters) and description.');
        }

        // Strict Date Verification based on logic
        if ($status === 'Currently Working') {
            if ($start === null) {
                throw new InvalidArgumentException('Start date is required when currently working on a project.');
            }
            $end = null; // Enforce no end date
        } elseif ($status === 'Completed') {
            if ($start === null || $end === null) {
                throw new InvalidArgumentException('Both start and end dates are required for completed projects.');
            }
            if ($end < $start) {
                throw new InvalidArgumentException('The end date cannot be earlier than the start date.');
            }
        }

        // Duplication Check
        if ($id === null) {
            $check = $this->db->prepare('SELECT portfolio_project_id FROM student_portfolio_projects WHERE student_email = ? AND LOWER(title) = LOWER(?)');
            $check->bind_param('ss', $email, $title);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $check->close();
                throw new InvalidArgumentException('You have already added a project with this name.');
            }
            $check->close();
        } else {
            if (!$this->ownsProject($email, $id)) {
                throw new RuntimeException('Project not found or access denied.');
            }
            $check = $this->db->prepare('SELECT portfolio_project_id FROM student_portfolio_projects WHERE student_email = ? AND LOWER(title) = LOWER(?) AND portfolio_project_id != ?');
            $check->bind_param('ssi', $email, $title, $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $check->close();
                throw new InvalidArgumentException('Another project with this name already exists in your portfolio.');
            }
            $check->close();
        }

        $this->db->begin_transaction();
        try {
            if ($id === null) {
                $stmt = $this->db->prepare('INSERT INTO student_portfolio_projects
                    (student_email, title, description, repository_url, project_image, project_status, start_date, end_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssssss', $email, $title, $description, $url, $image, $status, $start, $end);
                $stmt->execute();
                $id = (int) $this->db->insert_id;
            } else {
                if (!empty($image)) {
                    $stmtOld = $this->db->prepare('SELECT project_image FROM student_portfolio_projects WHERE portfolio_project_id = ?');
                    $stmtOld->bind_param('i', $id);
                    $stmtOld->execute();
                    $oldImage = $stmtOld->get_result()->fetch_column();
                    $stmtOld->close();
                    
                    if (!empty($oldImage) && $oldImage !== $image) {
                        $oldPath = __DIR__ . '/../../' . $oldImage;
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                }

                $stmt = $this->db->prepare('UPDATE student_portfolio_projects SET title = ?, description = ?,
                    repository_url = ?, project_image = COALESCE(?, project_image), project_status = ?, start_date = ?, end_date = ?
                    WHERE portfolio_project_id = ? AND student_email = ?');
                $stmt->bind_param('sssssssis', $title, $description, $url, $image, $status, $start, $end, $id, $email);
                $stmt->execute();
            }
            $stmt->close();
            $this->replaceTechnologies($id, $technologyNames);
            $this->db->commit();
            return $id;
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }
    }

    public function ownsProject(string $email, int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM student_portfolio_projects WHERE portfolio_project_id = ? AND student_email = ?');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        $found = (bool) $stmt->get_result()->fetch_row();
        $stmt->close();
        return $found;
    }

    public function deleteProject(string $email, int $id): bool
    {
        $stmt = $this->db->prepare('SELECT project_image FROM student_portfolio_projects WHERE portfolio_project_id = ? AND student_email = ?');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM student_portfolio_projects WHERE portfolio_project_id = ? AND student_email = ?');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if ($deleted && !empty($row['project_image'])) {
            $filePath = __DIR__ . '/../../' . $row['project_image'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        return $deleted;
    }

    public function internalProjects(string $email): array
    {
        $sql = "SELECT sp.student_project_id, p.id AS organization_project_id,
                       p.title AS original_title, p.description AS original_description,
                       p.status AS original_status, org.Name AS company,
                       COALESCE((SELECT GROUP_CONCAT(ps.skill_name ORDER BY ps.skill_name SEPARATOR ', ')
                           FROM project_skills ps WHERE ps.project_id = p.id), '') AS tech_stack,
                       sp.role, sp.progress, sp.status AS student_status,
                       ov.display_title, ov.display_description, ov.hidden,
                       (SELECT t.id FROM org_teams t JOIN org_team_members tm ON tm.team_id = t.id WHERE t.project_id = p.id AND LOWER(tm.Email) = LOWER(sp.Email) ORDER BY t.id DESC LIMIT 1) AS team_id
                FROM student_projects sp
                INNER JOIN projects p ON p.id = sp.project_id
                LEFT JOIN organization org ON org.Email = p.organization_email
                LEFT JOIN student_internal_portfolio_overrides ov
                    ON ov.student_project_id = sp.student_project_id AND ov.student_email = sp.Email
                WHERE LOWER(sp.Email) = LOWER(?)
                  AND p.visibility = 'Public'
                  AND LOWER(p.status) NOT IN ('hold', 'rejected', 'suspended', 'reviewing')
                  AND (ov.hidden IS NULL OR ov.hidden = 0)
                  AND (
                      LOWER(COALESCE(sp.status, '')) IN ('completed', 'complete', 'done')
                      OR LOWER(COALESCE(p.status, '')) IN ('completed', 'complete', 'done')
                      OR EXISTS (
                          SELECT 1 FROM org_teams t
                          JOIN org_team_members tm ON tm.team_id = t.id
                          WHERE t.project_id = p.id
                            AND LOWER(tm.Email) = LOWER(sp.Email)
                            AND LOWER(COALESCE(t.status, '')) IN ('completed', 'complete', 'done')
                      )
                  )
                ORDER BY sp.student_project_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($rows as &$row) {
            $row['source'] = 'internal';
            $row['id'] = (int) $row['student_project_id'];
            $row['title'] = $row['display_title'] ?: $row['original_title'];
            $row['description'] = $row['display_description'] ?? $row['original_description'];
            $row['technologies'] = array_values(array_filter(array_map('trim', explode(',', (string) $row['tech_stack']))));
            $row['project_status'] = $row['student_status'] ?: $row['original_status'];
            $row['repository_url'] = null;
            $row['project_image'] = null;
            $row['team_id'] = (int) ($row['team_id'] ?? 0);
        }
        unset($row);
        return $rows;
    }

    public function editInternalPresentation(string $email, int $id, string $title, string $description): void
    {
        $title = trim($title);
        $description = trim($description);
        $titleLength = function_exists('mb_strlen') ? mb_strlen($title) : strlen($title);
        $descriptionLength = function_exists('mb_strlen') ? mb_strlen($description) : strlen($description);
        if ($title === '' || $titleLength > 150 || $descriptionLength > 10000) {
            throw new InvalidArgumentException('Enter a title (up to 150 characters) and a shorter description.');
        }
        $stmt = $this->db->prepare('SELECT 1 FROM student_projects WHERE student_project_id = ? AND Email = ?');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        $exists = (bool) $stmt->get_result()->fetch_row();
        $stmt->close();
        if (!$exists) {
            throw new InvalidArgumentException('This project is not assigned to your account.');
        }
        $stmt = $this->db->prepare('INSERT INTO student_internal_portfolio_overrides
            (student_project_id, student_email, display_title, display_description, hidden)
            VALUES (?, ?, ?, ?, 0)
            ON DUPLICATE KEY UPDATE display_title = VALUES(display_title),
                display_description = VALUES(display_description), hidden = 0');
        $stmt->bind_param('isss', $id, $email, $title, $description);
        $stmt->execute();
        $stmt->close();
    }

    public function hideInternalProject(string $email, int $id): void
    {
        $stmt = $this->db->prepare('INSERT INTO student_internal_portfolio_overrides
            (student_project_id, student_email, hidden)
            SELECT student_project_id, Email, 1 FROM student_projects
            WHERE student_project_id = ? AND Email = ?
            ON DUPLICATE KEY UPDATE hidden = 1');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            throw new RuntimeException('Assigned project not found.');
        }
        $stmt->close();
    }

    public function certificates(string $email): array
    {
        $stmt = $this->db->prepare('SELECT certificate_id, certificate_name, issuer, certificate_file,
            issue_date, expiration_date, credential_id, credential_url
            FROM certificates WHERE Email = ? ORDER BY certificate_id DESC');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $skillsTableCheck = $this->db->query("SHOW TABLES LIKE 'student_certificate_skills'");
        $hasSkillsTable = $skillsTableCheck && $skillsTableCheck->num_rows > 0;

        if ($hasSkillsTable) {
            $skillsStmt = $this->db->prepare('SELECT skill_name FROM student_certificate_skills
                WHERE certificate_id = ? ORDER BY skill_name');
            foreach ($rows as &$row) {
                $id = (int) $row['certificate_id'];
                $skillsStmt->bind_param('i', $id);
                $skillsStmt->execute();
                $row['skills'] = array_column($skillsStmt->get_result()->fetch_all(MYSQLI_ASSOC), 'skill_name');
            }
            unset($row);
            $skillsStmt->close();
        } else {
            foreach ($rows as &$row) {
                $row['skills'] = [];
            }
            unset($row);
        }

        return $rows;
    }

    public function saveCertificate(string $email, array $data, ?int $id = null): int
    {
        $name = trim((string) ($data['certificate_name'] ?? ''));
        $issuer = trim((string) ($data['issuer'] ?? ''));
        $issue = trim((string) ($data['issue_date'] ?? '')) ?: null;
        $expiration = trim((string) ($data['expiration_date'] ?? '')) ?: null;
        $credentialId = trim((string) ($data['credential_id'] ?? ''));
        $url = trim((string) ($data['credential_url'] ?? ''));
        $file = trim((string) ($data['certificate_file'] ?? '')) ?: null;
        
        if (strlen($name) < 2 || strlen($name) > 150) {
            throw new InvalidArgumentException('Enter a valid Certificate name.');
        }

        if ($issue !== null && $expiration !== null && $expiration < $issue) {
            throw new InvalidArgumentException('Expiration cannot be before issue date.');
        }
        
        // Duplication Check
        if ($id === null) {
            $check = $this->db->prepare('SELECT certificate_id FROM certificates WHERE Email = ? AND LOWER(certificate_name) = LOWER(?)');
            $check->bind_param('ss', $email, $name);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $check->close();
                throw new InvalidArgumentException('You have already added a certificate with this name.');
            }
            $check->close();
        } else {
            if (!$this->ownsCertificate($email, $id)) {
                throw new RuntimeException('Certificate not found or access denied.');
            }
            $check = $this->db->prepare('SELECT certificate_id FROM certificates WHERE Email = ? AND LOWER(certificate_name) = LOWER(?) AND certificate_id != ?');
            $check->bind_param('ssi', $email, $name, $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $check->close();
                throw new InvalidArgumentException('Another certificate with this name already exists in your portfolio.');
            }
            $check->close();
        }

        $this->db->begin_transaction();
        try {
            if ($id === null) {
                $stmt = $this->db->prepare('INSERT INTO certificates
                    (Email, certificate_name, issuer, certificate_file, issue_date, expiration_date, credential_id, credential_url)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssssss', $email, $name, $issuer, $file, $issue, $expiration, $credentialId, $url);
                $stmt->execute();
                $id = (int) $this->db->insert_id;
            } else {
                if (!empty($file)) {
                    $stmtOld = $this->db->prepare('SELECT certificate_file FROM certificates WHERE certificate_id = ?');
                    $stmtOld->bind_param('i', $id);
                    $stmtOld->execute();
                    $oldFile = $stmtOld->get_result()->fetch_column();
                    $stmtOld->close();
                    
                    if (!empty($oldFile) && $oldFile !== $file) {
                        $oldPath = __DIR__ . '/../../' . $oldFile;
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                }

                $stmt = $this->db->prepare('UPDATE certificates SET certificate_name = ?, issuer = ?,
                    certificate_file = COALESCE(?, certificate_file), issue_date = ?, expiration_date = ?,
                    credential_id = ?, credential_url = ? WHERE certificate_id = ? AND Email = ?');
                $stmt->bind_param('sssssssis', $name, $issuer, $file, $issue, $expiration, $credentialId, $url, $id, $email);
                $stmt->execute();
            }
            $stmt->close();
            $this->db->commit();
            return $id;
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }
    }

    public function ownsCertificate(string $email, int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM certificates WHERE certificate_id = ? AND Email = ?');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        $found = (bool) $stmt->get_result()->fetch_row();
        $stmt->close();
        return $found;
    }

    public function deleteCertificate(string $email, int $id): bool
    {
        $stmt = $this->db->prepare('SELECT certificate_file FROM certificates WHERE certificate_id = ? AND Email = ?');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM certificates WHERE certificate_id = ? AND Email = ?');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if ($deleted && !empty($row['certificate_file'])) {
            $filePath = __DIR__ . '/../../' . $row['certificate_file'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        return $deleted;
    }

    public function feedbacks(string $email): array
    {
        $rows = [];
        try {
            $sql = "SELECT pf.id, pf.rating, pf.summary AS feedback_text, pf.created_at,
                           COALESCE(tm.role, 'Team Member') AS role,
                           COALESCE(org.Name, 'Organization') AS company,
                           COALESCE(p.title, 'SkillBridge Project') AS project_title,
                           org.logo AS company_logo
                    FROM project_feedback pf
                    INNER JOIN org_teams t ON t.id = pf.team_id
                    INNER JOIN org_team_members tm ON tm.team_id = t.id
                    INNER JOIN projects p ON p.id = t.project_id
                    LEFT JOIN organization org ON org.Email = p.organization_email
                    WHERE LOWER(TRIM(tm.Email)) = LOWER(TRIM(?))
                    ORDER BY pf.created_at DESC, pf.id DESC";
            
            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        } catch (Throwable $e) {
            error_log('Feedback query error: ' . $e->getMessage());
        }
        return $rows;
    }
}