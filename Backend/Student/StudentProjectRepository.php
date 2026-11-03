<?php
declare(strict_types=1);

class StudentProjectRepository
{
    private mysqli $db;
    private array $columns = [];
    private array $tables = [];

    public function __construct(mysqli $db)
    {
        $this->db = $db;
        $this->ensureSavedProjectsTable();
    }

    private function ensureSavedProjectsTable(): void
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS student_saved_projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_email VARCHAR(100) NOT NULL,
                project_id INT NOT NULL,
                saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_student_saved_proj (student_email, project_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->tables['student_saved_projects'] = true;
        } catch (Throwable $e) {
            // Ignore if DB user lacks CREATE privilege
        }
    }

    private function hasTable(string $table): bool
    {
        if (!array_key_exists($table, $this->tables)) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
            $stmt->bind_param('s', $table);
            $stmt->execute();
            $this->tables[$table] = (int) $stmt->get_result()->fetch_row()[0] > 0;
            $stmt->close();
        }
        return $this->tables[$table];
    }

    private function hasColumn(string $table, string $column): bool
    {
        $key = "$table.$column";
        if (!array_key_exists($key, $this->columns)) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
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

        try {
            if ($types !== '') {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            return $result === false ? true : $result;
        } finally {
            $stmt->close();
        }
    }

    /**
     * Active working projects = projects where this student is a member of an active team (org_teams/org_team_members)
     * OR has an active 'In Progress' record in student_projects.
     */
    private function buildStudentActiveSql(string $escEmail): string
    {
        if ($escEmail === '') return '1 = 0';
        $parts = [];
        if ($this->hasTable('org_teams') && $this->hasTable('org_team_members')) {
            $parts[] = "EXISTS (SELECT 1 FROM org_teams t JOIN org_team_members tm ON tm.team_id = t.id WHERE t.project_id = p.id AND LOWER(tm.Email) = '{$escEmail}' AND LOWER(COALESCE(t.status, 'ontrack')) NOT IN ('completed', 'complete', 'done'))";
        }
        if ($this->hasTable('student_projects')) {
            $parts[] = "EXISTS (SELECT 1 FROM student_projects sp WHERE sp.project_id = p.id AND LOWER(sp.Email) = '{$escEmail}' AND LOWER(COALESCE(sp.status, '')) IN ('in progress', 'inprogress', 'active', 'ontrack'))";
        }
        return $parts ? '(' . implode(' OR ', $parts) . ')' : '1 = 0';
    }

    /**
     * Completed projects for this student
     */
    private function buildStudentCompletedSql(string $escEmail): string
    {
        if ($escEmail === '') return '1 = 0';
        $parts = [];
        if ($this->hasTable('org_teams') && $this->hasTable('org_team_members')) {
            $parts[] = "EXISTS (SELECT 1 FROM org_teams t JOIN org_team_members tm ON tm.team_id = t.id WHERE t.project_id = p.id AND LOWER(tm.Email) = '{$escEmail}' AND LOWER(COALESCE(t.status, '')) IN ('completed', 'complete', 'done'))";
        }
        if ($this->hasTable('student_projects')) {
            $parts[] = "EXISTS (SELECT 1 FROM student_projects sp WHERE sp.project_id = p.id AND LOWER(sp.Email) = '{$escEmail}' AND (LOWER(COALESCE(sp.status, '')) IN ('completed', 'complete', 'done') OR LOWER(COALESCE(p.status, '')) IN ('completed', 'complete', 'done')))";
        }
        return $parts ? '(' . implode(' OR ', $parts) . ')' : '1 = 0';
    }

    /**
     * Proposal submitted by this student (via proposal.php -> project_proposals table).
     * Note: We do NOT block students based on legacy seed rows in project_applications unless they are already in a team/active project,
     * so students can properly apply and submit proposals for projects they haven't submitted a proposal to!
     */
    private function buildStudentProposalSubmittedSql(string $escEmail): string
    {
        if ($escEmail === '') return '1 = 0';
        if ($this->hasTable('project_proposals')) {
            return "EXISTS (SELECT 1 FROM project_proposals pp WHERE pp.project_id = p.id AND LOWER(pp.student_email) = '{$escEmail}')";
        }
        if ($this->hasTable('project_applications')) {
            return "EXISTS (SELECT 1 FROM project_applications pa WHERE pa.project_id = p.id AND LOWER(pa.Email) = '{$escEmail}')";
        }
        return '1 = 0';
    }

    /**
     * Not Applied Yet = Public published projects where the student has NOT submitted a proposal,
     * is NOT currently working on it, and has NOT completed it.
     */
    private function buildStudentNotAppliedSql(string $escEmail): string
    {
        if ($escEmail === '') return '1 = 1';
        $conds = [];
        if ($this->hasTable('project_proposals')) {
            $conds[] = "NOT EXISTS (SELECT 1 FROM project_proposals pp WHERE pp.project_id = p.id AND LOWER(pp.student_email) = '{$escEmail}')";
        } elseif ($this->hasTable('project_applications')) {
            $conds[] = "NOT EXISTS (SELECT 1 FROM project_applications pa WHERE pa.project_id = p.id AND LOWER(pa.Email) = '{$escEmail}')";
        }
        if ($this->hasTable('student_projects')) {
            $conds[] = "NOT EXISTS (SELECT 1 FROM student_projects sp WHERE sp.project_id = p.id AND LOWER(sp.Email) = '{$escEmail}')";
        }
        if ($this->hasTable('org_teams') && $this->hasTable('org_team_members')) {
            $conds[] = "NOT EXISTS (SELECT 1 FROM org_teams t JOIN org_team_members tm ON tm.team_id = t.id WHERE t.project_id = p.id AND LOWER(tm.Email) = '{$escEmail}')";
        }
        return $conds ? '(' . implode(' AND ', $conds) . ')' : '1 = 1';
    }

    private function baseSelect(string $email = ''): string
    {
        $category = $this->hasColumn('projects', 'category_id') && $this->hasTable('project_categories')
            ? 'pc.category_name' : ($this->hasColumn('projects', 'category') ? 'p.category' : "''");
        $duration = $this->hasColumn('projects', 'duration_weeks')
            ? "CONCAT(p.duration_weeks, ' weeks')" : ($this->hasColumn('projects', 'duration') ? 'p.duration' : "''");
        $company = $this->hasColumn('projects', 'company') ? 'COALESCE(NULLIF(p.company, \'\'), o.Name)' : 'o.Name';
        $tags = $this->hasTable('project_skills')
            ? "(SELECT GROUP_CONCAT(ps.skill_name ORDER BY ps.skill_name SEPARATOR ', ') FROM project_skills ps WHERE ps.project_id = p.id)"
            : ($this->hasColumn('projects', 'tech_stack') ? 'p.tech_stack' : "''");
        $categoryJoin = $this->hasColumn('projects', 'category_id') && $this->hasTable('project_categories')
            ? 'LEFT JOIN project_categories pc ON pc.category_id = p.category_id' : '';
        $deadline = $this->hasColumn('projects', 'deadline') ? 'p.deadline' : 'NULL';

        $escEmail = $this->db->real_escape_string(strtolower(trim($email)));

        $teamIdExpr = "NULL";
        if ($escEmail !== '' && $this->hasTable('org_teams') && $this->hasTable('org_team_members')) {
            $teamIdExpr = "(SELECT t.id FROM org_teams t JOIN org_team_members tm ON tm.team_id = t.id WHERE t.project_id = p.id AND LOWER(tm.Email) = '{$escEmail}' ORDER BY t.id DESC LIMIT 1)";
        }

        $activeExpr = $escEmail !== '' ? $this->buildStudentActiveSql($escEmail) : '0';
        $completedExpr = $escEmail !== '' ? $this->buildStudentCompletedSql($escEmail) : '0';
        $appliedExpr = $escEmail !== '' ? $this->buildStudentProposalSubmittedSql($escEmail) : '0';

        return "SELECT p.id, p.title, p.description, p.learning_objectives, p.expected_outcomes,
            p.status, p.visibility, p.difficulty, p.preferred_year, p.members, p.posted_at,
            p.organization_email, $deadline AS deadline, $company AS organization_name,
            $category AS category_name, $duration AS duration_label,
            $tags AS technology_tags,
            $teamIdExpr AS student_team_id,
            $activeExpr AS is_student_active,
            $completedExpr AS is_student_completed,
            $appliedExpr AS is_student_applied
            FROM projects p
            LEFT JOIN organization o ON o.Email = p.organization_email
            $categoryJoin";
    }

    private function visibilityCondition(): string
    {
        return "p.visibility = 'Public' AND LOWER(p.status) NOT IN ('hold', 'rejected', 'suspended', 'reviewing')";
    }

    private function whereFilters(array $filters, string &$types, array &$params, string $email = ''): string
    {
        $where = [$this->visibilityCondition()];
        $escEmail = $this->db->real_escape_string(strtolower(trim($email)));

        $filterStatus = strtolower(trim((string) ($filters['filter_status'] ?? 'all')));
        if ($filterStatus === 'active') {
            $where[] = $this->buildStudentActiveSql($escEmail);
        } elseif ($filterStatus === 'not_applied') {
            $where[] = $this->buildStudentNotAppliedSql($escEmail);
        }

        if (!empty($filters['search'])) {
            $where[] = '(p.title LIKE ? OR p.description LIKE ? OR o.Name LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            array_push($params, $search, $search, $search);
            $types .= 'sss';
        }
        if (!empty($filters['category'])) {
            $column = $this->hasColumn('projects', 'category_id') && $this->hasTable('project_categories')
                ? 'pc.category_name' : 'p.category';
            if ($column !== 'p.category' || $this->hasColumn('projects', 'category')) {
                $where[] = "$column = ?";
                $types .= 's';
                $params[] = $filters['category'];
            }
        }
        if (!empty($filters['difficulty'])) {
            $where[] = 'p.difficulty = ?';
            $types .= 's';
            $params[] = $filters['difficulty'];
        }
        return implode(' AND ', $where);
    }

    public function categories(): array
    {
        if ($this->hasTable('project_categories') && $this->hasColumn('projects', 'category_id')) {
            return array_column($this->query('SELECT category_name FROM project_categories ORDER BY category_name')->fetch_all(MYSQLI_ASSOC), 'category_name');
        }
        if ($this->hasColumn('projects', 'category')) {
            return array_column($this->query("SELECT DISTINCT category FROM projects WHERE category IS NOT NULL AND category <> '' ORDER BY category")->fetch_all(MYSQLI_ASSOC), 'category');
        }
        return [];
    }

    public function count(array $filters = [], string $email = ''): int
    {
        $types = '';
        $params = [];
        $where = $this->whereFilters($filters, $types, $params, $email);
        $select = $this->baseSelect($email);
        $from = substr($select, strpos($select, 'FROM projects p'));
        return (int) $this->query("SELECT COUNT(DISTINCT p.id) $from WHERE $where", $types, $params)->fetch_row()[0];
    }

    public function stats(string $email = ''): array
    {
        $base = $this->count([], $email);
        $escEmail = $this->db->real_escape_string(strtolower(trim($email)));

        if ($escEmail !== '') {
            $vis = $this->visibilityCondition();
            $activeCond = $this->buildStudentActiveSql($escEmail);
            $active = (int) $this->query("SELECT COUNT(DISTINCT p.id) FROM projects p WHERE $vis AND $activeCond")->fetch_row()[0];

            $compCond = $this->buildStudentCompletedSql($escEmail);
            $completed = (int) $this->query("SELECT COUNT(DISTINCT p.id) FROM projects p WHERE $vis AND $compCond")->fetch_row()[0];

            return ['available' => $base, 'active' => $active, 'completed' => $completed];
        }

        $rows = $this->query("SELECT LOWER(status) AS state, COUNT(*) AS total FROM projects WHERE visibility = 'Public' AND LOWER(status) NOT IN ('hold', 'rejected', 'suspended', 'reviewing') GROUP BY LOWER(status)")->fetch_all(MYSQLI_ASSOC);
        $active = 0;
        $completed = 0;
        foreach ($rows as $row) {
            if (in_array($row['state'], ['inprogress', 'in progress', 'active'], true)) $active += (int) $row['total'];
            if ($row['state'] === 'completed') $completed += (int) $row['total'];
        }
        return ['available' => $base, 'active' => $active, 'completed' => $completed];
    }

    public function list(array $filters, int $page, int $limit = 9, string $email = ''): array
    {
        $types = '';
        $params = [];
        $where = $this->whereFilters($filters, $types, $params, $email);
        $offset = (max(1, $page) - 1) * $limit;
        $params[] = $limit;
        $params[] = $offset;
        return $this->query($this->baseSelect($email) . " WHERE $where ORDER BY p.posted_at DESC, p.id DESC LIMIT ? OFFSET ?", $types . 'ii', $params)->fetch_all(MYSQLI_ASSOC);
    }

    public function find(int $id, string $email = ''): ?array
    {
        $row = $this->query($this->baseSelect($email) . ' WHERE p.id = ? AND ' . $this->visibilityCondition() . ' LIMIT 1', 'i', [$id])->fetch_assoc();
        return $row ?: null;
    }

    public function savedIds(string $email): array
    {
        if (!$this->hasTable('student_saved_projects')) return [];
        return array_map('intval', array_column($this->query('SELECT project_id FROM student_saved_projects WHERE LOWER(student_email) = LOWER(?)', 's', [$email])->fetch_all(MYSQLI_ASSOC), 'project_id'));
    }

    public function saved(string $email): array
    {
        if (!$this->hasTable('student_saved_projects')) {
            return [];
        }
        return $this->query($this->baseSelect($email) . ' INNER JOIN student_saved_projects sp ON sp.project_id = p.id WHERE LOWER(sp.student_email) = LOWER(?) AND ' . $this->visibilityCondition() . ' ORDER BY sp.saved_at DESC', 's', [$email])->fetch_all(MYSQLI_ASSOC);
    }

    public function toggleSaved(string $email, int $id): bool
    {
        $this->ensureSavedProjectsTable();
        if (!$this->find($id, $email)) throw new InvalidArgumentException('Project not found or unavailable.');
        $existing = $this->query('SELECT COUNT(*) FROM student_saved_projects WHERE LOWER(student_email) = LOWER(?) AND project_id = ?', 'si', [$email, $id])->fetch_row()[0];
        if ($existing) {
            $this->query('DELETE FROM student_saved_projects WHERE LOWER(student_email) = LOWER(?) AND project_id = ?', 'si', [$email, $id]);
            return false;
        }
        $this->query('INSERT INTO student_saved_projects (student_email, project_id) VALUES (?, ?)', 'si', [$email, $id]);
        return true;
    }

    /**
     * Checks if student already submitted a proposal (or is already working on / in a team for this project).
     */
    public function alreadyApplied(string $email, int $projectId): bool
    {
        if ($this->hasTable('project_proposals')) {
            $count = (int) $this->query('SELECT COUNT(*) FROM project_proposals WHERE LOWER(student_email) = LOWER(?) AND project_id = ?', 'si', [$email, $projectId])->fetch_row()[0];
            if ($count > 0) return true;
        } elseif ($this->hasTable('project_applications')) {
            $count = (int) $this->query('SELECT COUNT(*) FROM project_applications WHERE LOWER(Email) = LOWER(?) AND project_id = ?', 'si', [$email, $projectId])->fetch_row()[0];
            if ($count > 0) return true;
        }
        if ($this->hasTable('org_teams') && $this->hasTable('org_team_members')) {
            $count = (int) $this->query("SELECT COUNT(*) FROM org_teams t JOIN org_team_members tm ON tm.team_id = t.id WHERE LOWER(tm.Email) = LOWER(?) AND t.project_id = ? AND LOWER(COALESCE(t.status, 'ontrack')) NOT IN ('completed', 'complete', 'done')", 'si', [$email, $projectId])->fetch_row()[0];
            if ($count > 0) return true;
        }
        if ($this->hasTable('student_projects')) {
            $count = (int) $this->query("SELECT COUNT(*) FROM student_projects WHERE LOWER(Email) = LOWER(?) AND project_id = ? AND LOWER(COALESCE(status, '')) IN ('in progress', 'inprogress', 'active', 'ontrack')", 'si', [$email, $projectId])->fetch_row()[0];
            if ($count > 0) return true;
        }
        return false;
    }

    public function submitProposal(string $email, int $projectId, array $data): void
    {
        if (!$this->find($projectId, $email)) throw new InvalidArgumentException('Project not found or unavailable.');
        if ($this->alreadyApplied($email, $projectId)) throw new InvalidArgumentException('You have already applied to this project.');
        if ($this->hasTable('project_proposals')) {
            $stmt = $this->db->prepare('INSERT INTO project_proposals (project_id, student_email, proposal_title, target_role, introduction, solution_approach, portfolio_attachment) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('issssss', $projectId, $email, $data['title'], $data['role'], $data['introduction'], $data['approach'], $data['attachment']);
            $stmt->execute();
            $proposalId = $this->db->insert_id;
            $stmt->close();
            if ($this->hasTable('proposal_skills') && !empty($data['skills'])) {
                foreach ($data['skills'] as $skill) {
                    $s = $this->db->prepare('INSERT IGNORE INTO proposal_skills (proposal_id, skill_name) VALUES (?, ?)');
                    $s->bind_param('is', $proposalId, $skill);
                    $s->execute();
                    $s->close();
                }
            }
            if ($this->hasTable('project_applications')) {
                $chk = (int) $this->query('SELECT COUNT(*) FROM project_applications WHERE LOWER(Email) = LOWER(?) AND project_id = ?', 'si', [$email, $projectId])->fetch_row()[0];
                if ($chk === 0) {
                    $pending = 'pending';
                    $pa = $this->db->prepare('INSERT INTO project_applications (project_id, Email, status) VALUES (?, ?, ?)');
                    if ($pa) {
                        $pa->bind_param('iss', $projectId, $email, $pending);
                        $pa->execute();
                        $pa->close();
                    }
                }
            }
        } else {
            $supportsAttachment = $this->hasColumn('project_applications', 'portfolio_attachment');
            $sql = $supportsAttachment
                ? 'INSERT INTO project_applications (project_id, Email, status, portfolio_attachment) VALUES (?, ?, ?, ?)'
                : 'INSERT INTO project_applications (project_id, Email, status) VALUES (?, ?, ?)';
            $stmt = $this->db->prepare($sql);
            $pending = 'pending';
            if ($supportsAttachment) {
                $stmt->bind_param('isss', $projectId, $email, $pending, $data['attachment']);
            } else {
                $stmt->bind_param('iss', $projectId, $email, $pending);
            }
            $stmt->execute();
            $stmt->close();
        }
    }

    public function supportsProposalAttachment(): bool
    {
        return $this->hasTable('project_proposals')
            ? $this->hasColumn('project_proposals', 'portfolio_attachment')
            : $this->hasColumn('project_applications', 'portfolio_attachment');
    }

    public function supportsDetailedProposal(): bool
    {
        return $this->hasTable('project_proposals');
    }
}
