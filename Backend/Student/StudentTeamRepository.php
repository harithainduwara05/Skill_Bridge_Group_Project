<?php
declare(strict_types=1);

require_once __DIR__ . '/../TeamWorkspace.php';

final class StudentTeamRepository
{
    public function __construct(private mysqli $db)
    {
    }

    private function rows(string $sql, string $types = '', array $values = []): array
    {
        $stmt = $this->db->prepare($sql);
        try {
            if ($types !== '') {
                $stmt->bind_param($types, ...$values);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } finally {
            $stmt->close();
        }
    }

    private function hasTable(string $table): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->bind_param('s', $table);
        $stmt->execute();
        $found = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
        $stmt->close();
        return $found;
    }

    private function hasColumn(string $table, string $column): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        $found = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
        $stmt->close();
        return $found;
    }

    /**
     * Automatically ensures org_teams and org_team_members exist for any active/completed student_projects
     * so students can always open their Team & Workspace from Active Projects or My Teams!
     */
    public function ensureTeamsFromStudentProjects(string $email): void
    {
        if (!$this->hasTable('student_projects') || !$this->hasTable('org_teams') || !$this->hasTable('org_team_members')) {
            return;
        }
        $projects = $this->rows(
            "SELECT DISTINCT sp.project_id, p.title, p.deadline, p.status AS project_status
             FROM student_projects sp
             JOIN projects p ON p.id = sp.project_id
             WHERE LOWER(sp.Email) = LOWER(?)",
            's',
            [$email]
        );

        foreach ($projects as $pr) {
            $pid = (int) $pr['project_id'];
            $inTeam = $this->rows(
                "SELECT t.id FROM org_teams t
                 JOIN org_team_members m ON m.team_id = t.id
                 WHERE t.project_id = ? AND LOWER(m.Email) = LOWER(?)",
                'is',
                [$pid, $email]
            );
            if ($inTeam) {
                continue;
            }

            $existingTeam = $this->rows("SELECT id FROM org_teams WHERE project_id = ? ORDER BY id ASC LIMIT 1", 'i', [$pid]);
            if ($existingTeam) {
                $teamId = (int) $existingTeam[0]['id'];
                $roleRow = $this->rows("SELECT role FROM student_projects WHERE project_id = ? AND LOWER(Email) = LOWER(?) LIMIT 1", 'is', [$pid, $email]);
                $role = $roleRow[0]['role'] ?? 'Member';
                $ins = $this->db->prepare("INSERT IGNORE INTO org_team_members (team_id, Email, role) VALUES (?, ?, ?)");
                $ins->bind_param('iss', $teamId, $email, $role);
                $ins->execute();
                $ins->close();
                twRefreshStatus($this->db, $teamId);
            } else {
                $assigned = $this->rows("SELECT Email, role, status FROM student_projects WHERE project_id = ? ORDER BY student_project_id ASC", 'i', [$pid]);
                if (!$assigned) continue;

                $leaderEmail = strtolower((string) $assigned[0]['Email']);
                $teamName = mb_substr(trim((string) $pr['title']) . ' Team', 0, 100);
                $deadline = !empty($pr['deadline']) ? $pr['deadline'] : date('Y-m-d', strtotime('+30 days'));
                $allCompleted = true;
                foreach ($assigned as $a) {
                    if (strtolower((string) $a['status']) !== 'completed') {
                        $allCompleted = false;
                    }
                }
                $initStatus = $allCompleted ? 'completed' : 'ontrack';

                $insTeam = $this->db->prepare("INSERT INTO org_teams (project_id, name, leader_email, deadline, status) VALUES (?, ?, ?, ?, ?)");
                $insTeam->bind_param('issss', $pid, $teamName, $leaderEmail, $deadline, $initStatus);
                if ($insTeam->execute()) {
                    $newTeamId = (int) $this->db->insert_id;
                    $insMem = $this->db->prepare("INSERT IGNORE INTO org_team_members (team_id, Email, role) VALUES (?, ?, ?)");
                    foreach ($assigned as $a) {
                        $mEmail = strtolower(trim((string) $a['Email']));
                        $mRole = trim((string) ($a['role'] ?: ($mEmail === $leaderEmail ? 'Team Leader' : 'Member')));
                        $insMem->bind_param('iss', $newTeamId, $mEmail, $mRole);
                        $insMem->execute();
                    }
                    $insMem->close();
                    if (!$allCompleted) {
                        twRefreshStatus($this->db, $newTeamId);
                    }
                }
                $insTeam->close();
            }
        }
    }

    public function teams(string $email): array
    {
        $this->ensureTeamsFromStudentProjects($email);

        $organization = $this->hasColumn('projects', 'company') ? 'p.company' : 'o.Name';
        $skills = $this->hasColumn('org_teams', 'skills') ? 't.skills' : 'NULL';
        $technology = $this->hasColumn('projects', 'tech_stack') ? 'p.tech_stack' : '(SELECT GROUP_CONCAT(ps.skill_name SEPARATOR ", ") FROM project_skills ps WHERE ps.project_id = p.id)';
        $organizationJoin = $this->hasColumn('projects', 'company') ? '' : 'LEFT JOIN organization o ON o.Email = p.organization_email';
        $sql = "SELECT t.id, t.name, t.status, t.deadline, t.leader_email, t.project_id, t.created_at,
                       {$skills} AS team_skills, {$technology} AS tech_stack,
                       p.title AS project_title, p.description AS project_description,
                       {$organization} AS organization_name, m.role AS member_role,
                       COALESCE(s.Name, t.leader_email) AS leader_name,
                       (SELECT COUNT(*) FROM org_team_members x WHERE x.team_id = t.id) AS member_count,
                       (SELECT ROUND(AVG(sp.progress)) FROM student_projects sp
                        JOIN org_team_members om ON LOWER(om.Email) = LOWER(sp.Email) AND om.team_id = t.id
                        WHERE sp.project_id = t.project_id) AS sp_progress
                FROM org_team_members m
                JOIN org_teams t ON t.id = m.team_id
                JOIN projects p ON p.id = t.project_id
                {$organizationJoin}
                LEFT JOIN student s ON LOWER(s.Email) = LOWER(t.leader_email)
                WHERE LOWER(m.Email) = LOWER(?)
                ORDER BY t.created_at DESC, t.id DESC";
        $teams = $this->rows($sql, 's', [$email]);
        foreach ($teams as &$team) {
            $tid = (int) $team['id'];
            twRefreshStatus($this->db, $tid);

            $stRow = $this->rows("SELECT status FROM org_teams WHERE id = ?", 'i', [$tid]);
            if ($stRow) {
                $team['status'] = $stRow[0]['status'];
            }

            $teamTasks = twTasks($this->db, $tid);
            $doneTasks = count(array_filter($teamTasks, static fn($tk) => $tk['col'] === 'done'));
            if ($teamTasks) {
                $team['progress'] = (int) round(($doneTasks / count($teamTasks)) * 100);
            } else {
                $team['progress'] = max(0, min(100, (int) ($team['sp_progress'] ?? 0)));
            }
            if (strtolower((string) $team['status']) === 'completed') {
                $team['progress'] = 100;
            }

            $team['is_leader'] = strtolower(trim($email)) === strtolower(trim((string) $team['leader_email']));
            $tags = (string) ($team['team_skills'] ?: $team['tech_stack'] ?: '');
            $team['technologies'] = array_values(array_filter(array_map('trim', explode(',', $tags))));
        }
        unset($team);
        return $teams;
    }

    public function team(string $email, int $teamId): ?array
    {
        foreach ($this->teams($email) as $team) {
            if ((int) $team['id'] === $teamId) {
                return $team;
            }
        }
        return null;
    }

    public function teamByProject(string $email, int $projectId): ?array
    {
        foreach ($this->teams($email) as $team) {
            if ((int) $team['project_id'] === $projectId) {
                return $team;
            }
        }
        return null;
    }

    public function members(string $email, int $teamId): array
    {
        return $this->rows('SELECT m.Email, m.role, COALESCE(s.Name, m.Email) AS name, s.profile_image
            FROM org_team_members m LEFT JOIN student s ON LOWER(s.Email) = LOWER(m.Email)
            WHERE m.team_id = ? AND EXISTS (
                SELECT 1 FROM org_team_members access WHERE access.team_id = m.team_id AND LOWER(access.Email) = LOWER(?)
            ) ORDER BY name', 'is', [$teamId, $email]);
    }

    public function tasks(string $email, int $teamId): array
    {
        if (!$this->hasTable('team_tasks') || !$this->team($email, $teamId)) {
            return [];
        }
        return $this->rows('SELECT t.task_id, t.title, t.description, t.status, t.due_date, LOWER(t.assigned_to) AS assigned_to, COALESCE(s.Name, t.assigned_to) AS assignee_name FROM team_tasks t LEFT JOIN student s ON LOWER(s.Email) = LOWER(t.assigned_to) WHERE t.team_id = ? ORDER BY t.task_id ASC', 'i', [$teamId]);
    }

    public function files(string $email, int $teamId): array
    {
        if (!$this->hasTable('team_files') || !$this->team($email, $teamId)) {
            return [];
        }
        $team = $this->team($email, $teamId);
        $canDeleteAll = !empty($team['is_leader']);
        return twFiles($this->db, $teamId, $email, $canDeleteAll);
    }

    public function messages(string $email, int $teamId): array
    {
        if (!$this->hasTable('team_messages') || !$this->team($email, $teamId)) {
            return [];
        }
        return twMessages($this->db, $teamId, 0, $email);
    }
}
