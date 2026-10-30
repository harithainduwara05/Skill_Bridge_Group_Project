<?php
final class StudentPortfolioRepository
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
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

    public function countProjects(string $email): int
    {
        return $this->scalar('SELECT COUNT(*) FROM student_portfolio_projects WHERE student_email = ?', $email);
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
        if (!is_array($technologyNames) || count($technologyNames) > 40) {
            throw new InvalidArgumentException('Invalid technology list.');
        }
        if ($image !== null && ($image !== basename($image) || strlen($image) > 255)) {
            throw new InvalidArgumentException('Invalid project image filename.');
        }
        if (strlen($title) < 2 || strlen($title) > 150 || strlen($description) > 10000) {
            throw new InvalidArgumentException('Enter a valid project title (2–150 characters) and description.');
        }
        if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true))) {
            throw new InvalidArgumentException('Project link must be a valid HTTP(S) URL.');
        }
        if (strlen($url) > 500 || !in_array($status, ['Completed', 'Currently Working'], true)) {
            throw new InvalidArgumentException('Invalid project URL or status.');
        }
        foreach ([$start, $end] as $date) {
            if ($date !== null && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || DateTimeImmutable::createFromFormat('!Y-m-d', $date)?->format('Y-m-d') !== $date)) {
                throw new InvalidArgumentException('Invalid project date.');
            }
        }
        if ($start !== null && $end !== null && $end < $start) {
            throw new InvalidArgumentException('Project end date must be after the start date.');
        }
        // Reject ownership violations before updating any technology links.
        if ($id !== null && !$this->ownsProject($email, $id)) {
            throw new RuntimeException('Project not found or access denied.');
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
                $stmt = $this->db->prepare('UPDATE student_portfolio_projects SET title = ?, description = ?,
                    repository_url = ?, project_image = COALESCE(?, project_image), project_status = ?, start_date = ?, end_date = ?
                    WHERE portfolio_project_id = ? AND student_email = ?');
                $stmt->bind_param('sssssssis', $title, $description, $url, $image, $status, $start, $end, $id, $email);
                $stmt->execute();
                if ($stmt->affected_rows === 0 && !$this->ownsProject($email, $id)) {
                    throw new RuntimeException('Project not found.');
                }
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
        $stmt = $this->db->prepare('DELETE FROM student_portfolio_projects WHERE portfolio_project_id = ? AND student_email = ?');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();
        return $deleted;
    }

    /** Student-owned organization project records, with optional portfolio-only overrides. */
    public function internalProjects(string $email): array
    {
        $sql = "SELECT sp.student_project_id, p.id AS organization_project_id,
                       p.title AS original_title, p.description AS original_description,
                       p.status AS original_status, org.Name AS company,
                       COALESCE((SELECT GROUP_CONCAT(ps.skill_name ORDER BY ps.skill_name SEPARATOR ', ')
                           FROM project_skills ps WHERE ps.project_id = p.id), '') AS tech_stack,
                       sp.role, sp.progress, sp.status AS student_status,
                       ov.display_title, ov.display_description, ov.hidden
                FROM student_projects sp
                INNER JOIN projects p ON p.id = sp.project_id
                LEFT JOIN organization org ON org.Email = p.organization_email
                LEFT JOIN student_internal_portfolio_overrides ov
                    ON ov.student_project_id = sp.student_project_id AND ov.student_email = sp.Email
                WHERE sp.Email = ? AND (ov.hidden IS NULL OR ov.hidden = 0)
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
        }
        unset($row);
        return $rows;
    }

    /** Only changes this student's portfolio presentation, never the shared project. */
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
            status, issue_date, expiration_date, credential_id, credential_url
            FROM certificates WHERE Email = ? ORDER BY certificate_id DESC');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
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
        $skillsText = trim((string) ($data['skills'] ?? ''));
        if ($file !== null && ($file !== basename($file) || strlen($file) > 255)) {
            throw new InvalidArgumentException('Invalid certificate filename.');
        }
        if (strlen($name) < 2 || strlen($name) > 150 || strlen($issuer) < 2 || strlen($issuer) > 150 || strlen($credentialId) > 150) {
            throw new InvalidArgumentException('Certificate name and issuer must be 2–150 characters.');
        }
        foreach ([$issue, $expiration] as $date) {
            if ($date !== null && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || DateTimeImmutable::createFromFormat('!Y-m-d', $date)?->format('Y-m-d') !== $date)) {
                throw new InvalidArgumentException('Invalid certificate date.');
            }
        }
        if ($issue !== null && $expiration !== null && $expiration < $issue) {
            throw new InvalidArgumentException('Expiration cannot be before issue date.');
        }
        if (strlen($url) > 500 || ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)))) {
            throw new InvalidArgumentException('Invalid credential URL.');
        }
        if (strlen($skillsText) > 1500) {
            throw new InvalidArgumentException('Skills field is too long.');
        }
        $skills = [];
        foreach (explode(',', $skillsText) as $skill) {
            $skill = trim($skill);
            if ($skill === '') {
                continue;
            }
            if (strlen($skill) > 100) {
                throw new InvalidArgumentException('Each skill must be 100 characters or fewer.');
            }
            $skills[strtolower($skill)] = $skill;
        }
        if ($id !== null && !$this->ownsCertificate($email, $id)) {
            throw new RuntimeException('Certificate not found or access denied.');
        }
        $this->db->begin_transaction();
        try {
            if ($id === null) {
                $stmt = $this->db->prepare('INSERT INTO certificates
                    (Email, certificate_name, issuer, certificate_file, status, issue_date, expiration_date, credential_id, credential_url)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $status = 'Pending';
                $stmt->bind_param('sssssssss', $email, $name, $issuer, $file, $status, $issue, $expiration, $credentialId, $url);
                $stmt->execute();
                $id = (int) $this->db->insert_id;
            } else {
                $stmt = $this->db->prepare('UPDATE certificates SET certificate_name = ?, issuer = ?,
                    certificate_file = COALESCE(?, certificate_file), issue_date = ?, expiration_date = ?,
                    credential_id = ?, credential_url = ? WHERE certificate_id = ? AND Email = ?');
                $stmt->bind_param('sssssssis', $name, $issuer, $file, $issue, $expiration, $credentialId, $url, $id, $email);
                $stmt->execute();
            }
            $stmt->close();
            $clear = $this->db->prepare('DELETE FROM student_certificate_skills WHERE certificate_id = ?');
            $clear->bind_param('i', $id);
            $clear->execute();
            $clear->close();
            if ($skills) {
                $insert = $this->db->prepare('INSERT INTO student_certificate_skills (certificate_id, skill_name) VALUES (?, ?)');
                foreach ($skills as $skill) {
                    $insert->bind_param('is', $id, $skill);
                    $insert->execute();
                }
                $insert->close();
            }
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
        $stmt = $this->db->prepare('DELETE FROM certificates WHERE certificate_id = ? AND Email = ?');
        $stmt->bind_param('is', $id, $email);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();
        return $deleted;
    }
}
