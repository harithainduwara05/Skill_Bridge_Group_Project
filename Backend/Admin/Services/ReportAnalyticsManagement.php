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
        return (int) $this->runCount("SELECT COUNT(*) FROM internships WHERE LOWER(status) = 'active'");
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
        $sql = "SELECT u.University, u.Location, u.faculty,
                    COUNT(DISTINCT s.Email) AS student_count,
                    (SELECT COUNT(*) FROM projects p WHERE u.emailEx != '' AND p.organization_email LIKE CONCAT('%', u.emailEx, '%')) AS project_count,
                    COUNT(DISTINCT CASE WHEN LOWER(ia.status) = 'accepted' THEN ia.application_id END) AS placed_count
                FROM universityemails u
                LEFT JOIN student s ON LOWER(s.University) LIKE CONCAT('%', LOWER(SUBSTRING_INDEX(u.University, ' ', 3)), '%')
                LEFT JOIN internship_applications ia ON s.Email = ia.Email
                GROUP BY u.University, u.Location, u.faculty, u.emailEx
                ORDER BY student_count DESC";

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
        $sql = "SELECT c.Name, c.companytype, c.location, 
                    COUNT(DISTINCT i.id) AS vacancies_count, 
                    COUNT(ia.application_id) AS applications_count, 
                    COALESCE(SUM(CASE WHEN LOWER(ia.status) = 'accepted' THEN 1 ELSE 0 END), 0) AS hired_count 
                FROM company c 
                LEFT JOIN internships i ON TRIM(LOWER(c.Name)) = TRIM(LOWER(i.company)) 
                LEFT JOIN internship_applications ia ON i.id = ia.internship_id 
                GROUP BY c.Email, c.Name, c.companytype, c.location 
                ORDER BY vacancies_count DESC";

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
        $sql = "SELECT DATE_FORMAT(applied_date, '%b %Y') AS m_label, COUNT(*) AS apps,
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
        $res = $this->runQuery("SELECT tech_tags AS tags FROM internships WHERE tech_tags IS NOT NULL UNION ALL SELECT tech_stack AS tags FROM projects WHERE tech_stack IS NOT NULL");
        $totalPosts = (int) $this->runCount("SELECT (SELECT COUNT(*) FROM internships) + (SELECT COUNT(*) FROM projects)");
        $allTags = [];

        if ($res) {
            while ($r = $res->fetch_assoc()) {
                foreach (preg_split('/[,•|\/]/', $r['tags']) as $t) {
                    $t = trim($t);
                    if ($t !== '' && !is_numeric($t) && strlen($t) > 1) {
                        $allTags[] = $t;
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
        $res = $this->runQuery("SELECT COUNT(*) AS total,
                    SUM(CASE WHEN LOWER(status) IN ('under review', 'accepted') THEN 1 ELSE 0 END) AS review_apps,
                    SUM(CASE WHEN interview_date IS NOT NULL OR LOWER(status) IN ('under review', 'accepted') THEN 1 ELSE 0 END) AS interview_apps,
                    SUM(CASE WHEN LOWER(status) = 'accepted' THEN 1 ELSE 0 END) AS hired_apps
                FROM internship_applications");

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
