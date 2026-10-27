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

            // INSERT, UPDATE and DELETE do not produce a result set.
            return $result === false ? true : $result;
        } finally {
            $stmt->close();
        }
    }

    private function baseSelect(): string
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
        return "SELECT p.id, p.title, p.description, p.learning_objectives, p.expected_outcomes,
            p.status, p.visibility, p.difficulty, p.preferred_year, p.members, p.posted_at,
            p.organization_email, $deadline AS deadline, $company AS organization_name,
            $category AS category_name, $duration AS duration_label,
            $tags AS technology_tags
            FROM projects p
            LEFT JOIN organization o ON o.Email = p.organization_email
            $categoryJoin";
    }

    private function visibilityCondition(): string
    {
        return "p.visibility = 'Public' AND LOWER(p.status) NOT IN ('hold', 'rejected', 'suspended')";
    }

    private function whereFilters(array $filters, string &$types, array &$params): string
    {
        $where = [$this->visibilityCondition()];
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

    public function count(array $filters = []): int
    {
        $types = '';
        $params = [];
        $where = $this->whereFilters($filters, $types, $params);
        $select = $this->baseSelect();
        $from = substr($select, strpos($select, 'FROM projects p'));
        return (int) $this->query("SELECT COUNT(DISTINCT p.id) $from WHERE $where", $types, $params)->fetch_row()[0];
    }

    public function stats(): array
    {
        $base = $this->count();
        $rows = $this->query("SELECT LOWER(status) AS state, COUNT(*) AS total FROM projects WHERE visibility = 'Public' GROUP BY LOWER(status)")->fetch_all(MYSQLI_ASSOC);
        $active = 0;
        $completed = 0;
        foreach ($rows as $row) {
            if (in_array($row['state'], ['reviewing', 'inprogress', 'in progress', 'active'], true)) $active += (int) $row['total'];
            if ($row['state'] === 'completed') $completed += (int) $row['total'];
        }
        return ['available' => $base, 'active' => $active, 'completed' => $completed];
    }

    public function list(array $filters, int $page, int $limit = 9): array
    {
        $types = '';
        $params = [];
        $where = $this->whereFilters($filters, $types, $params);
        $offset = (max(1, $page) - 1) * $limit;
        $params[] = $limit;
        $params[] = $offset;
        return $this->query($this->baseSelect() . " WHERE $where ORDER BY p.posted_at DESC, p.id DESC LIMIT ? OFFSET ?", $types . 'ii', $params)->fetch_all(MYSQLI_ASSOC);
    }

    public function find(int $id): ?array
    {
        $row = $this->query($this->baseSelect() . ' WHERE p.id = ? AND ' . $this->visibilityCondition() . ' LIMIT 1', 'i', [$id])->fetch_assoc();
        return $row ?: null;
    }

    public function savedIds(string $email): array
    {
        if (!$this->hasTable('student_saved_projects')) return [];
        return array_map('intval', array_column($this->query('SELECT project_id FROM student_saved_projects WHERE student_email = ?', 's', [$email])->fetch_all(MYSQLI_ASSOC), 'project_id'));
    }

    public function saved(string $email): array
    {
        if (!$this->hasTable('student_saved_projects')) {
            throw new RuntimeException('Saved projects table is missing. Import the included bookmark migration.');
        }
        return $this->query($this->baseSelect() . ' INNER JOIN student_saved_projects sp ON sp.project_id = p.id WHERE sp.student_email = ? AND ' . $this->visibilityCondition() . ' ORDER BY sp.saved_at DESC', 's', [$email])->fetch_all(MYSQLI_ASSOC);
    }

    public function toggleSaved(string $email, int $id): bool
    {
        if (!$this->hasTable('student_saved_projects')) {
            throw new RuntimeException('Saved projects table is missing. Import the included bookmark migration.');
        }
        if (!$this->find($id)) throw new InvalidArgumentException('Project not found or unavailable.');
        $existing = $this->query('SELECT COUNT(*) FROM student_saved_projects WHERE student_email = ? AND project_id = ?', 'si', [$email, $id])->fetch_row()[0];
        if ($existing) {
            $this->query('DELETE FROM student_saved_projects WHERE student_email = ? AND project_id = ?', 'si', [$email, $id]);
            return false;
        }
        $this->query('INSERT INTO student_saved_projects (student_email, project_id) VALUES (?, ?)', 'si', [$email, $id]);
        return true;
    }

    public function alreadyApplied(string $email, int $projectId): bool
    {
        if ($this->hasTable('project_proposals')) {
            $count = (int) $this->query('SELECT COUNT(*) FROM project_proposals WHERE student_email = ? AND project_id = ?', 'si', [$email, $projectId])->fetch_row()[0];
            if ($count > 0) return true;
        }
        if ($this->hasTable('project_applications')) {
            return (int) $this->query('SELECT COUNT(*) FROM project_applications WHERE Email = ? AND project_id = ?', 'si', [$email, $projectId])->fetch_row()[0] > 0;
        }
        return false;
    }

    public function submitProposal(string $email, int $projectId, array $data): void
    {
        if (!$this->find($projectId)) throw new InvalidArgumentException('Project not found or unavailable.');
        if ($this->alreadyApplied($email, $projectId)) throw new InvalidArgumentException('You have already applied to this project.');
        if ($this->hasTable('project_proposals')) {
            $stmt = $this->db->prepare('INSERT INTO project_proposals (project_id, student_email, proposal_title, target_role, introduction, solution_approach, portfolio_attachment) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('issssss', $projectId, $email, $data['title'], $data['role'], $data['introduction'], $data['approach'], $data['attachment']);
            $stmt->execute();
            $proposalId = $this->db->insert_id;
            if ($this->hasTable('proposal_skills')) {
                foreach ($data['skills'] as $skill) {
                    $s = $this->db->prepare('INSERT IGNORE INTO proposal_skills (proposal_id, skill_name) VALUES (?, ?)');
                    $s->bind_param('is', $proposalId, $skill);
                    $s->execute();
                }
            }
        } else {
            // Legacy schema supports application status only; do not pretend to store full proposal text.
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
