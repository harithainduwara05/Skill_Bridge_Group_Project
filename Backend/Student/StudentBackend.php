<?php

declare(strict_types=1);

require_once __DIR__ . '/StudentPortfolioRepository.php';

class StudentManager
{
    private mysqli $db;
    private ?StudentPortfolioRepository $portfolioRepository = null;

    public function __construct(?mysqli $db = null)
    {
        if ($db === null) {
            global $conn;
            $db = $conn ?? null;
        }

        if (!$db instanceof mysqli) {
            throw new RuntimeException('SkillBridge database connection is not available.');
        }

        $this->db = $db;
    }

    private function fetchAll(string $sql, string $types = '', array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    private function count(string $sql, string $email): int
    {
        $rows = $this->fetchAll($sql, 's', [$email]);
        return (int) ($rows[0]['total'] ?? 0);
    }

    private function repository(): StudentPortfolioRepository
    {
        if ($this->portfolioRepository === null) {
            $this->portfolioRepository = new StudentPortfolioRepository($this->db);
        }
        return $this->portfolioRepository;
    }

    // ---------------------------------------------------------------------
    // Student profile and skills (compatible with existing Dashboard pages)
    // ---------------------------------------------------------------------

    public function getStudent(string $email): ?array
    {
        $rows = $this->fetchAll(
            'SELECT s.*, u.status AS user_status
             FROM student AS s
             INNER JOIN user AS u ON u.Email = s.Email
             WHERE s.Email = ? LIMIT 1',
            's',
            [$email]
        );
        return $rows[0] ?? null;
    }

    public function getSkillCount(string $email): int
    {
        return $this->count('SELECT COUNT(*) AS total FROM skills WHERE Email = ?', $email);
    }

    public function getAverageSkillPercentage(string $email): int
    {
        $rows = $this->fetchAll(
            'SELECT COALESCE(ROUND(AVG(percentage)), 0) AS average FROM skills WHERE Email = ?',
            's',
            [$email]
        );
        return (int) ($rows[0]['average'] ?? 0);
    }

    public function getTotalSkillExperience(string $email): int
    {
        $rows = $this->fetchAll(
            'SELECT initial_experience, created_at FROM skills WHERE Email = ?',
            's',
            [$email]
        );
        $today = new DateTimeImmutable();
        $total = 0;

        foreach ($rows as $skill) {
            $initial = max(0, (int) $skill['initial_experience']);
            try {
                $createdAt = new DateTimeImmutable((string) $skill['created_at']);
                $elapsed = $createdAt > $today ? 0 : $createdAt->diff($today)->y;
            } catch (Exception $exception) {
                $elapsed = 0;
            }
            $total += $initial + $elapsed;
        }
        return $total;
    }

    public function getSkills(string $email): array
    {
        return $this->fetchAll(
            'SELECT skill_id, skill_name, category, level, initial_experience,
                    percentage, created_at
             FROM skills WHERE Email = ? ORDER BY skill_id DESC',
            's',
            [$email]
        );
    }

    public function getCertificateCount(string $email): int
    {
        return $this->count('SELECT COUNT(*) AS total FROM certificates WHERE Email = ?', $email);
    }

    // Internal organization projects assigned to the student (legacy method).
    public function getProjectCount(string $email): int
    {
        return $this->count('SELECT COUNT(*) AS total FROM student_projects WHERE Email = ?', $email);
    }

    public function getProjects(string $email, int $limit = 2): array
    {
        $limit = max(1, min($limit, 100));
        return $this->fetchAll(
            'SELECT p.id, p.title, p.company AS organization, sp.role AS team,
                    sp.progress, sp.status
             FROM student_projects AS sp
             INNER JOIN projects AS p ON p.id = sp.project_id
             WHERE sp.Email = ?
             ORDER BY sp.student_project_id DESC LIMIT ?',
            'si',
            [$email, $limit]
        );
    }

    public function getActiveTeamCount(string $email): int
    {
        $rows = $this->fetchAll(
            "SELECT COUNT(DISTINCT t.id) AS total
             FROM org_teams AS t
             LEFT JOIN org_team_members AS m ON m.team_id = t.id AND m.Email = ?
             WHERE (m.Email IS NOT NULL OR t.leader_email = ?)
               AND t.status = 'ontrack'",
            'ss',
            [$email, $email]
        );
        return (int) ($rows[0]['total'] ?? 0);
    }

    public function getTeams(string $email): array
    {
        return $this->fetchAll(
            'SELECT DISTINCT t.id, t.name, t.status, t.deadline,
                    t.project_id, p.title AS project_title,
                    CASE WHEN t.leader_email = ? THEN \'Leader\' ELSE m.role END AS member_role
             FROM org_teams AS t
             INNER JOIN projects AS p ON p.id = t.project_id
             LEFT JOIN org_team_members AS m ON m.team_id = t.id AND m.Email = ?
             WHERE t.leader_email = ? OR m.Email = ?
             ORDER BY t.created_at DESC',
            'ssss',
            [$email, $email, $email, $email]
        );
    }

    // ---------------------------------------------------------------------
    // Internships, applications and bookmarks
    // ---------------------------------------------------------------------

    public function getApplicationCount(string $email): int
    {
        return $this->count('SELECT COUNT(*) AS total FROM internship_applications WHERE Email = ?', $email);
    }

    public function getRecommendedInternships(int $limit = 2): array
    {
        $limit = max(1, min($limit, 50));
        return $this->fetchAll(
            'SELECT id, title, company, industry, tech_tags, duration, deadline
             FROM internships ORDER BY id DESC LIMIT ?',
            'i',
            [$limit]
        );
    }

    public function getInternshipApplications(string $email): array
    {
        return $this->fetchAll(
            'SELECT a.application_id, a.internship_id, a.status,
                    a.applied_date, i.title, i.company, i.deadline
             FROM internship_applications AS a
             INNER JOIN internships AS i ON i.id = a.internship_id
             WHERE a.Email = ? ORDER BY a.applied_date DESC, a.application_id DESC',
            's',
            [$email]
        );
    }

    public function getProjectApplications(string $email): array
    {
        return $this->fetchAll(
            'SELECT a.id AS application_id, a.project_id, a.status,
                    a.applied_at, a.decided_at, p.title, p.company AS organization
             FROM project_applications AS a
             INNER JOIN projects AS p ON p.id = a.project_id
             WHERE a.Email = ? ORDER BY a.applied_at DESC, a.id DESC',
            's',
            [$email]
        );
    }

    public function getSavedInternships(string $email): array
    {
        return $this->fetchAll(
            'SELECT i.id, i.title, i.company, i.industry, i.tech_tags,
                    i.duration, i.deadline, s.saved_at
             FROM student_saved_internships AS s
             INNER JOIN internships AS i ON i.id = s.internship_id
             WHERE s.student_email = ? ORDER BY s.saved_at DESC',
            's',
            [$email]
        );
    }

    public function isInternshipSaved(string $email, int $internshipId): bool
    {
        $rows = $this->fetchAll(
            'SELECT 1 AS found FROM student_saved_internships
             WHERE student_email = ? AND internship_id = ? LIMIT 1',
            'si',
            [$email, $internshipId]
        );
        return (bool) $rows;
    }

    public function saveInternship(string $email, int $internshipId): void
    {
        if ($internshipId <= 0) {
            throw new InvalidArgumentException('Invalid internship.');
        }
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO student_saved_internships (student_email, internship_id)
             VALUES (?, ?)'
        );
        $stmt->bind_param('si', $email, $internshipId);
        $stmt->execute();
        $stmt->close();
    }

    public function removeSavedInternship(string $email, int $internshipId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM student_saved_internships WHERE student_email = ? AND internship_id = ?'
        );
        $stmt->bind_param('si', $email, $internshipId);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();
        return $deleted;
    }

    // ---------------------------------------------------------------------
    // Notifications
    // ---------------------------------------------------------------------

    public function getNotifications(string $email, int $limit = 3): array
    {
        $limit = max(1, min($limit, 100));
        return $this->fetchAll(
            'SELECT notification_id, title, message, type, status, created_at
             FROM notifications WHERE Email = ?
             ORDER BY created_at DESC, notification_id DESC LIMIT ?',
            'si',
            [$email, $limit]
        );
    }

    public function getUnreadNotificationCount(string $email): int
    {
        return $this->count(
            "SELECT COUNT(*) AS total FROM notifications WHERE Email = ? AND status = 'Unread'",
            $email
        );
    }

    public function markNotificationRead(string $email, int $notificationId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET status = 'Read'
             WHERE Email = ? AND notification_id = ?"
        );
        $stmt->bind_param('si', $email, $notificationId);
        $stmt->execute();
        $updated = $stmt->affected_rows > 0;
        $stmt->close();
        return $updated;
    }

    // ---------------------------------------------------------------------
    // Portfolio: own projects and organization-managed internal projects
    // ---------------------------------------------------------------------

    public function getPortfolioSummary(string $email): array
    {
        $summary = $this->repository()->summary($email);
        $summary['internal_projects'] = count($this->getInternalPortfolioProjects($email));
        $summary['total_projects'] = $summary['projects'] + $summary['internal_projects'];
        return $summary;
    }

    public function getPortfolioProjects(string $email, int $page = 1, int $perPage = 6): array
    {
        return $this->repository()->projects($email, max(1, $page), max(1, min(100, $perPage)));
    }

    public function getInternalPortfolioProjects(string $email): array
    {
        return $this->repository()->internalProjects($email);
    }

    public function savePortfolioProject(string $email, array $data, ?int $projectId = null): int
    {
        $data = array_merge([
            'title' => '', 'description' => '', 'repository_url' => '',
            'project_image' => null, 'project_status' => 'Currently Working',
            'start_date' => null, 'end_date' => null, 'technologies' => [],
        ], $data);
        return $this->repository()->saveProject($email, $data, $projectId);
    }

    public function deletePortfolioProject(string $email, int $projectId): bool
    {
        return $this->repository()->deleteProject($email, $projectId);
    }

    public function updateInternalPortfolioProject(
        string $email,
        int $studentProjectId,
        string $displayTitle,
        string $displayDescription
    ): void {
        $this->repository()->editInternalPresentation(
            $email,
            $studentProjectId,
            $displayTitle,
            $displayDescription
        );
    }

    public function hideInternalPortfolioProject(string $email, int $studentProjectId): void
    {
        $this->repository()->hideInternalProject($email, $studentProjectId);
    }

    public function getPortfolioCertificates(string $email): array
    {
        return $this->repository()->certificates($email);
    }

    public function savePortfolioCertificate(string $email, array $data, ?int $certificateId = null): int
    {
        $data = array_merge([
            'certificate_name' => '', 'issuer' => '', 'certificate_file' => null,
            'issue_date' => null, 'expiration_date' => null,
            'credential_id' => '', 'credential_url' => '',
        ], $data);
        return $this->repository()->saveCertificate($email, $data, $certificateId);
    }

    public function deletePortfolioCertificate(string $email, int $certificateId): bool
    {
        return $this->repository()->deleteCertificate($email, $certificateId);
    }

    public function getProfileViewCount(string $email): int
    {
        return $this->count(
            'SELECT COUNT(*) AS total FROM student_profile_views WHERE student_email = ?',
            $email
        );
    }

    /**
     * Call only from a public-profile request after you have resolved the
     * profile owner's email from the database. Never trust a posted owner email.
     * Avoid logging the owner's own page views at the caller.
     */
    public function recordProfileView(string $profileOwnerEmail): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO student_profile_views (student_email) VALUES (?)'
        );
        $stmt->bind_param('s', $profileOwnerEmail);
        $stmt->execute();
        $stmt->close();
    }
}
