<?php
/**
 * SkillBridge - Dashboard Service
 * Handles metrics and counts for active projects, internships, and platform operations.
 */

require_once __DIR__ . '/AdminDB.php';

class DashboardManagement extends AdminDB
{
    public function getOngoingProjectCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM projects WHERE STR_TO_DATE(deadline, '%b %d, %Y') >= CURRENT_DATE()");
    }

    public function getAvailableInternshipCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM internships WHERE STR_TO_DATE(deadline, '%b %d, %Y') >= CURRENT_DATE()");
    }

    public function getActiveProjectCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM student_projects WHERE status = 'Active'");
    }

    public function getPlacedStudentsCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM internship_applications WHERE LOWER(status) = 'accepted'");
    }

    public function getActiveDriveCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM internships WHERE LOWER(status) = 'active'");
    }

    public function getSupervisorReviewProjectCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM projects WHERE LOWER(status) = 'reviewing'");
    }

    public function getResolvedComplaintsCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM complain WHERE LOWER(status) IN ('resolved', 'dismissed')");
    }

    public function getOpenComplaintsCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM complain WHERE LOWER(status) NOT IN ('resolved', 'dismissed')");
    }

    public function getUniversityBenchmarks()
    {
        $unisRes = $this->runQuery("SELECT * FROM universityemails");
        $unis = $unisRes ? $unisRes->fetch_all(MYSQLI_ASSOC) : [];
        $benchmarks = [];

        foreach ($unis as $u) {
            $uName = $u['University'];
            $uDomain = $u['emailEx'] ?? '';

            $stCount = (int) $this->runCount(
                "SELECT COUNT(*) FROM student WHERE LOWER(University) LIKE CONCAT('%', LOWER(SUBSTRING_INDEX(?, ' ', 3)), '%')",
                "s",
                $uName
            );

            $projCount = 0;
            if (!empty($uDomain)) {
                $projCount = (int) $this->runCount(
                    "SELECT COUNT(*) FROM projects WHERE organization_email LIKE CONCAT('%', ?, '%')",
                    "s",
                    $uDomain
                );
            }

            $placedCount = (int) $this->runCount("
                SELECT COUNT(DISTINCT ia.application_id) 
                FROM internship_applications ia
                INNER JOIN student s ON ia.Email = s.Email
                WHERE LOWER(s.University) LIKE CONCAT('%', LOWER(SUBSTRING_INDEX(?, ' ', 3)), '%')
                AND LOWER(ia.status) = 'accepted'
            ", "s", $uName);

            $rate = ($stCount > 0) ? round(($placedCount / $stCount) * 100, 1) : 0;

            $benchmarks[] = [
                'University'     => $uName,
                'Location'       => $u['Location'] ?? 'N/A',
                'faculty'        => $u['faculty'] ?? '',
                'student_count'  => $stCount,
                'project_count'  => $projCount,
                'placed_count'   => $placedCount,
                'placement_rate' => $rate
            ];
        }

        // Sort by student count descending
        usort($benchmarks, fn($a, $b) => $b['student_count'] <=> $a['student_count']);
        return $benchmarks;
    }

    public function getCompanyBenchmarks()
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

    public function getComplaintsLog($limit = 100)
    {
        $sql = "SELECT id, email, category, title, status, create_at FROM complain ORDER BY id DESC LIMIT ?";
        $res = $this->runQuery($sql, "i", $limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getHiringVelocityData()
    {
        $sql = "SELECT 
                    DATE_FORMAT(applied_date, '%b %Y') as m_label,
                    COUNT(*) as apps,
                    SUM(CASE WHEN LOWER(status) = 'accepted' THEN 1 ELSE 0 END) as hired
                FROM internship_applications
                WHERE applied_date IS NOT NULL
                GROUP BY DATE_FORMAT(applied_date, '%Y-%m')
                ORDER BY MIN(applied_date) ASC";
        $res = $this->runQuery($sql);
        $data = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        if (empty($data)) {
            $data = [['m_label' => date('M Y'), 'apps' => 0, 'hired' => 0]];
        }
        return $data;
    }

    public function getStudentEnrolmentDistribution()
    {
        $totalStudents = (int) $this->runCount("SELECT COUNT(*) FROM student");
        $unisRes = $this->runQuery("SELECT * FROM universityemails");
        $unis = $unisRes ? $unisRes->fetch_all(MYSQLI_ASSOC) : [];

        $donutSegments = [];
        $circumference = 440;
        $offset = 0;
        $palette = ['#2563eb', '#f59e0b', '#8b5cf6', '#ef4444', '#10b981', '#64748b'];
        $idx = 0;

        foreach ($unis as $u) {
            $uName = $u['University'];
            $count = (int) $this->runCount(
                "SELECT COUNT(*) FROM student WHERE LOWER(University) LIKE CONCAT('%', LOWER(SUBSTRING_INDEX(?, ' ', 3)), '%')",
                "s",
                $uName
            );

            $pct = ($totalStudents > 0) ? round(($count / $totalStudents) * 100, 1) : 0;
            $dash = round(($pct / 100) * $circumference, 1);

            $donutSegments[] = [
                'name'    => $uName,
                'faculty' => $u['faculty'] ?? '',
                'count'   => $count,
                'pct'     => $pct,
                'dash'    => $dash,
                'offset'  => -$offset,
                'color'   => $palette[$idx % count($palette)]
            ];
            $offset += $dash;
            $idx++;
        }

        usort($donutSegments, fn($a, $b) => $b['count'] <=> $a['count']);

        return [
            'total_students' => $totalStudents,
            'segments'       => $donutSegments
        ];
    }

    public function getTopInDemandSkills($limit = 5)
    {
        $sql = "SELECT tech_tags as tags FROM internships WHERE tech_tags IS NOT NULL UNION ALL SELECT tech_stack as tags FROM projects WHERE tech_stack IS NOT NULL";
        $res = $this->runQuery($sql);
        $totalPosts = (int) $this->runCount("SELECT (SELECT COUNT(*) FROM internships) + (SELECT COUNT(*) FROM projects) as total");
        $skillCounts = [];

        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $tags = preg_split('/[,•|\/]/', $row['tags']);
                foreach ($tags as $t) {
                    $t = trim($t);
                    if (!empty($t) && !is_numeric($t) && strlen($t) > 1) {
                        $skillCounts[$t] = ($skillCounts[$t] ?? 0) + 1;
                    }
                }
            }
        }

        arsort($skillCounts);
        $top = [];
        $skillColors = ['fill-blue', 'fill-indigo', 'fill-green', 'fill-amber', 'fill-purple'];
        $sIdx = 0;

        foreach (array_slice($skillCounts, 0, $limit, true) as $skillName => $freq) {
            $pct = ($totalPosts > 0) ? min(100, round(($freq / $totalPosts) * 100)) : 0;
            $top[] = [
                'name'  => $skillName,
                'freq'  => $freq,
                'pct'   => $pct,
                'color' => $skillColors[$sIdx % count($skillColors)]
            ];
            $sIdx++;
        }

        return $top;
    }

    public function getRecruitmentFunnelData()
    {
        $totalApps     = (int) $this->runCount("SELECT COUNT(*) FROM internship_applications");
        $reviewApps    = (int) $this->runCount("SELECT COUNT(*) FROM internship_applications WHERE LOWER(status) IN ('under review', 'accepted')");
        $interviewApps = (int) $this->runCount("SELECT COUNT(*) FROM internship_applications WHERE interview_date IS NOT NULL OR LOWER(status) IN ('under review', 'accepted')");
        $hiredApps     = (int) $this->runCount("SELECT COUNT(*) FROM internship_applications WHERE LOWER(status) = 'accepted'");

        $step1Pct = 100;
        $step2Pct = ($totalApps > 0) ? round(($reviewApps / $totalApps) * 100, 1) : 0;
        $step3Pct = ($totalApps > 0) ? round(($interviewApps / $totalApps) * 100, 1) : 0;
        $step4Pct = ($totalApps > 0) ? round(($hiredApps / $totalApps) * 100, 1) : 0;

        return [
            ['step' => 'Step 1', 'label' => 'Applications',   'val' => $totalApps,     'pct' => $step1Pct . '% Volume'],
            ['step' => 'Step 2', 'label' => 'Under Review',   'val' => $reviewApps,    'pct' => $step2Pct . '% Screen'],
            ['step' => 'Step 3', 'label' => 'Interviews',     'val' => $interviewApps, 'pct' => $step3Pct . '% Shortlisted'],
            ['step' => 'Step 4', 'label' => 'Placed / Hired', 'val' => $hiredApps,     'pct' => $step4Pct . '% Conversion']
        ];
    }

    public function getPeriodDataBreakdown()
    {
        return [
            '30d'     => $this->calculatePeriodMetrics("DATE_SUB(NOW(), INTERVAL 30 DAY)"),
            'quarter' => $this->calculatePeriodMetrics("DATE_SUB(NOW(), INTERVAL 90 DAY)"),
            'ytd'     => $this->calculatePeriodMetrics("DATE_FORMAT(NOW(), '%Y-01-01')"),
            'all'     => $this->calculatePeriodMetrics(null)
        ];
    }

    protected function calculatePeriodMetrics($intervalSql = null)
    {
        $whereApp  = $intervalSql ? "WHERE applied_date >= $intervalSql" : "";
        $whereProj = $intervalSql ? "WHERE posted_at >= $intervalSql" : "";
        $whereComp = $intervalSql ? "WHERE create_at >= $intervalSql" : "";

        $placed    = (int) $this->runCount("SELECT COUNT(*) FROM internship_applications $whereApp " . ($whereApp ? "AND" : "WHERE") . " LOWER(status) = 'accepted'");
        $totalApps = (int) $this->runCount("SELECT COUNT(*) FROM internship_applications $whereApp");
        $pRate     = ($totalApps > 0) ? round(($placed / $totalApps) * 100, 1) : 0;

        $companies = (int) $this->runCount("SELECT COUNT(*) FROM company");
        $drives    = (int) $this->runCount("SELECT COUNT(*) FROM internships WHERE LOWER(status) = 'active'");

        $projects   = (int) $this->runCount("SELECT COUNT(*) FROM projects $whereProj");
        $projReview = (int) $this->runCount("SELECT COUNT(*) FROM projects $whereProj " . ($whereProj ? "AND" : "WHERE") . " LOWER(status) = 'reviewing'");

        $complaints     = (int) $this->runCount("SELECT COUNT(*) FROM complain $whereComp");
        $resComplaints  = (int) $this->runCount("SELECT COUNT(*) FROM complain $whereComp " . ($whereComp ? "AND" : "WHERE") . " LOWER(status) IN ('resolved', 'dismissed')");
        $openComplaints = (int) $this->runCount("SELECT COUNT(*) FROM complain $whereComp " . ($whereComp ? "AND" : "WHERE") . " LOWER(status) NOT IN ('resolved', 'dismissed')");
        $rRate          = ($complaints > 0) ? round(($resComplaints / $complaints) * 100, 1) : 100;

        $velocity = $this->getHiringVelocityData();
        $labels   = array_column($velocity, 'm_label');
        $appsArr  = array_map('intval', array_column($velocity, 'apps'));
        $hiredArr = array_map('intval', array_column($velocity, 'hired'));

        return [
            'placedStudents' => number_format($placed),
            'placementRate'  => $pRate . '% Placement Rate',
            'companies'      => number_format($companies) . ' Companies',
            'drives'         => number_format($drives) . ' Active Drives',
            'projects'       => number_format($projects) . ' Projects',
            'projectReview'  => number_format($projReview) . ' In Supervisor Review',
            'resolution'     => $rRate . '% Resolved',
            'complaintOpen'  => number_format($openComplaints) . ' Open in Queue',
            'monthlyLabels'  => $labels,
            'applications'   => $appsArr,
            'hired'          => $hiredArr
        ];
    }
}