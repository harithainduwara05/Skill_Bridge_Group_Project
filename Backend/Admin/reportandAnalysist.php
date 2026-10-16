<?php

require_once __DIR__ . '/loader.php';

$reportsDB = new ReportAnalyticsManagement($conn);
$adminDB   = $reportsDB;

// Handle Export Report (Standard CSV Generation & Download)
if (isset($_GET['action']) && $_GET['action'] === 'export_report') {
    $scope     = trim($_GET['scope'] ?? 'executive');
    $timeframe = trim($_GET['timeframe'] ?? '30d');

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="skillbridge_report_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $scope) . '_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');

    if ($scope === 'universities') {
        fputcsv($out, ['University / Institute', 'Location', 'Enrolled Students', 'Status']);
        $unis = $reportsDB->getUniversityBenchmarks();
        foreach ($unis as $u) {
            fputcsv($out, [$u['University'], $u['Location'] ?? 'N/A', (int)($u['student_count'] ?? 0), 'Active Partner']);
        }
    } elseif ($scope === 'companies' || $scope === 'placements') {
        fputcsv($out, ['Company Name', 'Industry Sector', 'Location', 'Active Vacancies', 'Applications Received', 'Hired / Placed']);
        $comps = $reportsDB->getCompanyBenchmarks();
        foreach ($comps as $c) {
            fputcsv($out, [$c['Name'], $c['companytype'], $c['location'], $c['vacancies_count'], $c['applications_count'], $c['hired_count']]);
        }
    } elseif ($scope === 'complaints') {
        fputcsv($out, ['ID', 'Email', 'Category', 'Title', 'Status', 'Date']);
        $cmps = $reportsDB->getComplaintsLog();
        foreach ($cmps as $cmp) {
            fputcsv($out, [$cmp['id'], $cmp['email'] ?? 'N/A', $cmp['category'] ?? 'General', $cmp['title'] ?? 'N/A', $cmp['status'] ?? 'Pending', $cmp['create_at'] ?? 'N/A']);
        }
    } else {
        // Executive Summary
        fputcsv($out, ['SKILLBRIDGE EXECUTIVE PERFORMANCE REPORT']);
        fputcsv($out, ['Generated Date', date('Y-m-d H:i:s')]);
        fputcsv($out, ['Time Horizon', $timeframe]);
        fputcsv($out, []);
        fputcsv($out, ['Metric', 'Value', 'Context']);
        
        $placed    = $reportsDB->getPlacedStudentsCount();
        $totalApps = $reportsDB->getCount('internship_applications');
        $pRate     = ($totalApps > 0) ? round(($placed / $totalApps) * 100, 1) : 0;
        fputcsv($out, ['Placed Students', $placed, $pRate . '% Placement Rate']);
        fputcsv($out, ['Active Industry Partners', $reportsDB->getCount('company') . ' Companies', $reportsDB->getActiveDriveCount() . ' Active Drives']);
        fputcsv($out, ['Industry Projects', $reportsDB->getCount('projects') . ' Projects', $reportsDB->getSupervisorReviewProjectCount() . ' In Supervisor Review']);
        
        $totComp = $reportsDB->getCount('complain');
        $resComp = $reportsDB->getResolvedComplaintsCount();
        $rRate   = ($totComp > 0) ? round(($resComp / $totComp) * 100, 1) : 100;
        fputcsv($out, ['Grievance Resolution', $rRate . '% Resolved', $reportsDB->getOpenComplaintsCount() . ' Open in Queue']);
        fputcsv($out, []);
        fputcsv($out, ['TOP RECRUITING COMPANIES']);
        fputcsv($out, ['Company Name', 'Industry', 'Vacancies', 'Applications', 'Hired']);
        $comps = $reportsDB->getCompanyBenchmarks();
        foreach ($comps as $c) {
            fputcsv($out, [$c['Name'], $c['companytype'], $c['vacancies_count'], $c['applications_count'], $c['hired_count']]);
        }
        fputcsv($out, []);
        fputcsv($out, ['PARTNER UNIVERSITIES BENCHMARK']);
        fputcsv($out, ['University', 'Location', 'Enrolled Students', 'Projects Hosted', 'Placed Students']);
        $unis = $reportsDB->getUniversityBenchmarks();
        foreach ($unis as $u) {
            fputcsv($out, [$u['University'], $u['Location'], $u['student_count'], $u['project_count'], $u['placed_count']]);
        }
    }

    fclose($out);
    exit();
}

// Executive KPI Summary Calculations
$placedStudentsCount = $reportsDB->getPlacedStudentsCount();
$totalApplications   = $reportsDB->getCount('internship_applications');
$placementRate       = ($totalApplications > 0) ? round(($placedStudentsCount / $totalApplications) * 100, 1) : 0;

$activeCompaniesCount = $reportsDB->getCount('company');
$activeDrivesCount    = $reportsDB->getActiveDriveCount();

$totalProjectsCount  = $reportsDB->getCount('projects');
$reviewProjectsCount = $reportsDB->getSupervisorReviewProjectCount();

$totalComplaintsCount = $reportsDB->getCount('complain');
$resolvedComplaints   = $reportsDB->getResolvedComplaintsCount();
$openComplaintsCount  = $reportsDB->getOpenComplaintsCount();
$resolutionRate       = ($totalComplaintsCount > 0) ? round(($resolvedComplaints / $totalComplaintsCount) * 100, 1) : 100;

// Deep-Dive Benchmark Datasets
$universityBenchmarks = $reportsDB->getUniversityBenchmarks();
$companyBenchmarks    = $reportsDB->getCompanyBenchmarks();

// Visual Analytics Pure Database Datasets
$hiringVelocity       = $reportsDB->getHiringVelocityData();
$donutDistribution    = $reportsDB->getStudentEnrolmentDistribution();
$topSkills            = $reportsDB->getTopInDemandSkills(5);
$recruitmentFunnel    = $reportsDB->getRecruitmentFunnelData();
$periodDataBreakdown  = $reportsDB->getPeriodDataBreakdown();
?>
