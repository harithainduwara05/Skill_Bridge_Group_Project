<?php
/**
 * SkillBridge - Reports & Analytics Service
 * Handles live metric calculations, audit benchmarks, chart data feeds, and report downloads.
 */

require_once __DIR__ . '/AdminDB.php';

class ReportAnalyticsManagement extends AdminDB
{
    public function getPlacedStudentsCount(): int
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM internship_applications WHERE LOWER(status) = 'accepted'");
    }

    public function getActiveDriveCount(): int
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM internships WHERE id NOT IN (SELECT internship_id FROM internship_moderation_logs WHERE status = 'Suspended')");
    }

    public function getSupervisorReviewProjectCount(): int
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM projects WHERE LOWER(status) = 'reviewing'");
    }

    public function getResolvedComplaintsCount(): int
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM complain WHERE LOWER(status) IN ('resolved', 'dismissed')");
    }

    public function getOpenComplaintsCount(): int
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM complain WHERE LOWER(status) NOT IN ('resolved', 'dismissed')");
    }

    public function getUniversityBenchmarks(): array
    {
        $sql = "
            SELECT 
                u.University, 
                u.Location, 
                u.faculty,
                (SELECT COUNT(*) FROM student s WHERE s.domain = u.emailEx OR s.University = u.University) AS student_count,
                (SELECT COUNT(*) FROM projects p WHERE u.emailEx != '' AND p.organization_email LIKE CONCAT('%', u.emailEx, '%')) AS project_count,
                (SELECT COUNT(*) 
                 FROM internship_applications ia 
                 INNER JOIN student s ON ia.Email = s.Email 
                 WHERE (s.domain = u.emailEx OR s.University = u.University) AND LOWER(ia.status) = 'accepted') AS placed_count
            FROM universityemails u
            ORDER BY student_count DESC
        ";

        $res = $this->runQuery($sql);
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        return array_map(function($r) {
            $st = (int)$r['student_count'];
            $pl = (int)$r['placed_count'];
            return [
                'University'     => $r['University'],
                'Location'       => $r['Location'] ?? 'N/A',
                'faculty'        => $r['faculty'] ?? '',
                'student_count'  => $st,
                'project_count'  => (int)$r['project_count'],
                'placed_count'   => $pl,
                'placement_rate' => ($st > 0) ? round(($pl / $st) * 100, 1) : 0
            ];
        }, $rows);
    }

    public function getCompanyBenchmarks(): array
    {
        $sql = "
            SELECT 
                c.Name, 
                c.companytype, 
                c.location, 
                (SELECT COUNT(*) FROM internships WHERE company_email = c.Email) AS vacancies_count,
                (SELECT COUNT(*) 
                 FROM internship_applications ia 
                 INNER JOIN internships i ON ia.internship_id = i.id 
                 WHERE i.company_email = c.Email) AS applications_count,
                (SELECT COUNT(*) 
                 FROM internship_applications ia 
                 INNER JOIN internships i ON ia.internship_id = i.id 
                 WHERE i.company_email = c.Email AND LOWER(ia.status) = 'accepted') AS hired_count
            FROM company c
            ORDER BY vacancies_count DESC
        ";

        $res = $this->runQuery($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getComplaintsLog(int $limit = 100): array
    {
        $res = $this->runQuery("SELECT id, email, category, title, status, create_at FROM complain ORDER BY id DESC LIMIT ?", "i", $limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getHiringVelocityData(): array
    {
        $sql = "SELECT DATE_FORMAT(MIN(applied_date), '%b %Y') AS m_label, COUNT(*) AS apps,
                       SUM(CASE WHEN LOWER(status) = 'accepted' THEN 1 ELSE 0 END) AS hired
                FROM internship_applications WHERE applied_date IS NOT NULL
                GROUP BY DATE_FORMAT(applied_date, '%Y-%m') ORDER BY MIN(applied_date) ASC";

        $res = $this->runQuery($sql);
        $data = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        return !empty($data) ? $data : [['m_label' => date('M Y'), 'apps' => 0, 'hired' => 0]];
    }

    public function getStudentEnrolmentDistribution(): array
    {
        $unis = $this->getUniversityBenchmarks();
        $totalStudents = array_sum(array_column($unis, 'student_count'));
        $palette = ['#2563eb', '#f59e0b', '#8b5cf6', '#ef4444', '#10b981', '#64748b'];
        $offset = 0;

        $segments = array_map(function($u, $idx) use ($totalStudents, &$offset, $palette) {
            $pct = ($totalStudents > 0) ? round(($u['student_count'] / $totalStudents) * 100, 1) : 0;
            $dash = round(($pct / 100) * 440, 1);
            $seg = [
                'name'    => $u['University'],
                'faculty' => $u['faculty'],
                'count'   => $u['student_count'],
                'pct'     => $pct,
                'dash'    => $dash,
                'offset'  => -$offset,
                'color'   => $palette[$idx % 6]
            ];
            $offset += $dash;
            return $seg;
        }, $unis, array_keys($unis));

        return ['total_students' => $totalStudents, 'segments' => $segments];
    }

    public function getTopInDemandSkills(int $limit = 5): array
    {
        // 3NF normalized query using INNER JOIN across tags and skills
        $sql = "
            SELECT t.tag_name AS skill_name 
            FROM internship_tech_tag_map tm 
            INNER JOIN internship_tech_tags t ON tm.tag_id = t.tag_id
            UNION ALL
            SELECT ps.skill_name 
            FROM project_skills ps
        ";
        $res = @$this->runQuery($sql);
        $totalPosts = (int) $this->runCount("SELECT (SELECT COUNT(*) FROM internships) + (SELECT COUNT(*) FROM projects)");
        $allTags = [];

        if ($res && $res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) {
                $name = trim($r['skill_name'] ?? '');
                if ($name !== '' && strlen($name) > 1) {
                    $allTags[] = $name;
                }
            }
        } else {
            // Graceful fallback if normalized tag mapping is empty
            $fallback = @$this->runQuery("SELECT title FROM projects UNION ALL SELECT title FROM internships");
            if ($fallback) {
                while ($r = $fallback->fetch_assoc()) {
                    $parts = preg_split('/\s+/', $r['title'] ?? '');
                    foreach ($parts as $p) {
                        if (strlen($p) > 3) $allTags[] = ucfirst(strtolower($p));
                    }
                }
            }
        }

        $counts = array_count_values($allTags);
        arsort($counts);
        $palette = ['fill-blue', 'fill-indigo', 'fill-green', 'fill-amber', 'fill-purple'];
        $top = [];
        $i = 0;

        foreach (array_slice($counts, 0, $limit, true) as $name => $freq) {
            $top[] = [
                'name'  => $name,
                'freq'  => $freq,
                'pct'   => ($totalPosts > 0) ? min(100, round(($freq / $totalPosts) * 100)) : 0,
                'color' => $palette[$i++ % 5]
            ];
        }
        return $top;
    }

    public function getRecruitmentFunnelData(): array
    {
        $sql = "
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN LOWER(status) IN ('under review', 'accepted') THEN 1 ELSE 0 END) AS review_apps,
                SUM(CASE WHEN application_id IN (SELECT application_id FROM internship_interviews) OR LOWER(status) IN ('under review', 'accepted') THEN 1 ELSE 0 END) AS interview_apps,
                SUM(CASE WHEN LOWER(status) = 'accepted' THEN 1 ELSE 0 END) AS hired_apps
            FROM internship_applications
        ";
        $res = $this->runQuery($sql);
        $row = $res ? $res->fetch_assoc() : [];
        $t = (int)($row['total'] ?? 0);
        $calcPct = fn($val) => ($t > 0) ? round(($val / $t) * 100, 1) : 0;

        return [
            ['step' => 'Step 1', 'label' => 'Applications',   'val' => $t,                                  'pct' => '100% Volume'],
            ['step' => 'Step 2', 'label' => 'Under Review',   'val' => (int)($row['review_apps'] ?? 0),    'pct' => $calcPct($row['review_apps'] ?? 0) . '% Screen'],
            ['step' => 'Step 3', 'label' => 'Interviews',     'val' => (int)($row['interview_apps'] ?? 0), 'pct' => $calcPct($row['interview_apps'] ?? 0) . '% Shortlisted'],
            ['step' => 'Step 4', 'label' => 'Placed / Hired', 'val' => (int)($row['hired_apps'] ?? 0),     'pct' => $calcPct($row['hired_apps'] ?? 0) . '% Conversion']
        ];
    }

    public function getPeriodDataBreakdown(): array
    {
        $comps = number_format($this->getCount('company')) . ' Companies';
        $drives = number_format($this->getActiveDriveCount()) . ' Active Drives';
        $velocity = $this->getHiringVelocityData();
        $labels = array_column($velocity, 'm_label');
        $appsArr = array_map('intval', array_column($velocity, 'apps'));
        $hiredArr = array_map('intval', array_column($velocity, 'hired'));

        $calc = function($intervalSql) use ($comps, $drives, $labels, $appsArr, $hiredArr) {
            $wApp  = $intervalSql ? "WHERE applied_date >= $intervalSql" : "";
            $wProj = $intervalSql ? "WHERE posted_at >= $intervalSql" : "";
            $wComp = $intervalSql ? "WHERE create_at >= $intervalSql" : "";

            $appRes = $this->runQuery("SELECT COUNT(*) AS total, SUM(CASE WHEN LOWER(status) = 'accepted' THEN 1 ELSE 0 END) AS placed FROM internship_applications $wApp");
            $appRow = $appRes ? $appRes->fetch_assoc() : [];
            $placed = (int)($appRow['placed'] ?? 0);
            $totalApps = (int)($appRow['total'] ?? 0);
            $pRate = ($totalApps > 0) ? round(($placed / $totalApps) * 100, 1) : 0;

            $projRes = $this->runQuery("SELECT COUNT(*) AS total, SUM(CASE WHEN LOWER(status) = 'reviewing' THEN 1 ELSE 0 END) AS in_review FROM projects $wProj");
            $projRow = $projRes ? $projRes->fetch_assoc() : [];

            $compRes = $this->runQuery("SELECT COUNT(*) AS total, SUM(CASE WHEN LOWER(status) IN ('resolved', 'dismissed') THEN 1 ELSE 0 END) AS resolved FROM complain $wComp");
            $compRow = $compRes ? $compRes->fetch_assoc() : [];
            $totComp = (int)($compRow['total'] ?? 0);
            $resComp = (int)($compRow['resolved'] ?? 0);
            $rRate = ($totComp > 0) ? round(($resComp / $totComp) * 100, 1) : 100;

            return [
                'placedStudents' => number_format($placed),
                'placementRate'  => $pRate . '% Placement Rate',
                'companies'      => $comps,
                'drives'         => $drives,
                'projects'       => number_format((int)($projRow['total'] ?? 0)) . ' Projects',
                'projectReview'  => number_format((int)($projRow['in_review'] ?? 0)) . ' In Supervisor Review',
                'resolution'     => $rRate . '% Resolved',
                'complaintOpen'  => number_format($totComp - $resComp) . ' Open in Queue',
                'monthlyLabels'  => $labels,
                'applications'   => $appsArr,
                'hired'          => $hiredArr
            ];
        };

        return [
            '30d'     => $calc("DATE_SUB(NOW(), INTERVAL 30 DAY)"),
            'quarter' => $calc("DATE_SUB(NOW(), INTERVAL 90 DAY)"),
            'ytd'     => $calc("DATE_FORMAT(NOW(), '%Y-01-01')"),
            'all'     => $calc(null)
        ];
    }
}
?>
