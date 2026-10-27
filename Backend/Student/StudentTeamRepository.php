<?php
declare(strict_types=1);

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
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

    public function teams(string $email): array
    {
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
                        JOIN org_team_members om ON om.Email = sp.Email AND om.team_id = t.id
                        WHERE sp.project_id = t.project_id) AS progress
                FROM org_team_members m
                JOIN org_teams t ON t.id = m.team_id
                JOIN projects p ON p.id = t.project_id
                {$organizationJoin}
                LEFT JOIN student s ON s.Email = t.leader_email
                WHERE m.Email = ?
                ORDER BY t.created_at DESC, t.id DESC";
        $teams = $this->rows($sql, 's', [$email]);
        foreach ($teams as &$team) {
            $team['progress'] = max(0, min(100, (int) ($team['progress'] ?? 0)));
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

    public function members(string $email, int $teamId): array
    {
        return $this->rows('SELECT m.Email, m.role, COALESCE(s.Name, m.Email) AS name, s.profile_image
            FROM org_team_members m LEFT JOIN student s ON s.Email = m.Email
            WHERE m.team_id = ? AND EXISTS (
                SELECT 1 FROM org_team_members access WHERE access.team_id = m.team_id AND access.Email = ?
            ) ORDER BY name', 'is', [$teamId, $email]);
    }

    public function tasks(string $email, int $teamId): array
    {
        if (!$this->hasTable('team_tasks') || !$this->team($email, $teamId)) {
            return [];
        }
        return $this->rows('SELECT task_id, title, description, status, due_date, assigned_to FROM team_tasks WHERE team_id = ? ORDER BY created_at DESC', 'i', [$teamId]);
    }

    public function files(string $email, int $teamId): array
    {
        if (!$this->hasTable('team_files') || !$this->team($email, $teamId)) {
            return [];
        }
        return $this->rows('SELECT original_name, file_size, uploaded_at, uploaded_by FROM team_files WHERE team_id = ? ORDER BY uploaded_at DESC LIMIT 20', 'i', [$teamId]);
    }

    public function messages(string $email, int $teamId): array
    {
        if (!$this->hasTable('team_messages') || !$this->team($email, $teamId)) {
            return [];
        }
        return $this->rows('SELECT m.message, m.sent_at, COALESCE(s.Name, m.sender_email) AS sender_name, m.sender_email FROM team_messages m LEFT JOIN student s ON s.Email = m.sender_email WHERE m.team_id = ? ORDER BY m.sent_at DESC LIMIT 30', 'i', [$teamId]);
    }
}
