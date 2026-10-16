<?php

require_once __DIR__ . '/loader.php';

$dashboardDB = new DashboardManagement($conn);
$adminDB     = $dashboardDB;

// Handle Export Report (Standard CSV Generation & Download)
if (isset($_GET['action']) && $_GET['action'] === 'export_report') {
    $scope     = trim($_GET['scope'] ?? 'executive');
    $timeframe = trim($_GET['timeframe'] ?? '30d');

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="skillbridge_report_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $scope) . '_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');

    if ($scope === 'universities') {
        fputcsv($out, ['University / Institute', 'Location', 'Enrolled Students', 'Status']);
        $unis = $dashboardDB->getUniversityBenchmarks();
        foreach ($unis as $u) {
            fputcsv($out, [$u['University'], $u['Location'] ?? 'N/A', (int)($u['student_count'] ?? 0), 'Active Partner']);
        }
    } elseif ($scope === 'companies' || $scope === 'placements') {
        fputcsv($out, ['Company Name', 'Industry Sector', 'Location', 'Active Vacancies', 'Applications Received', 'Hired / Placed']);
        $comps = $dashboardDB->getCompanyBenchmarks();
        foreach ($comps as $c) {
            fputcsv($out, [$c['Name'], $c['companytype'], $c['location'], $c['vacancies_count'], $c['applications_count'], $c['hired_count']]);
        }
    } elseif ($scope === 'complaints') {
        fputcsv($out, ['ID', 'User', 'Role', 'Category', 'Subject', 'Status', 'Date']);
        $cmps = $dashboardDB->getComplaintsLog();
        foreach ($cmps as $cmp) {
            fputcsv($out, [$cmp['id'], $cmp['user_name'] ?? 'N/A', $cmp['role'] ?? 'N/A', $cmp['category'] ?? 'General', $cmp['subject'] ?? 'N/A', $cmp['status'] ?? 'Pending', $cmp['created_at'] ?? 'N/A']);
        }
    } else {
        // Executive Summary
        fputcsv($out, ['SKILLBRIDGE EXECUTIVE PERFORMANCE REPORT']);
        fputcsv($out, ['Generated Date', date('Y-m-d H:i:s')]);
        fputcsv($out, ['Time Horizon', $timeframe]);
        fputcsv($out, []);
        fputcsv($out, ['Metric', 'Value', 'Context']);
        
        $placed    = $dashboardDB->getPlacedStudentsCount();
        $totalApps = $dashboardDB->getCount('internship_applications');
        $pRate     = ($totalApps > 0) ? round(($placed / $totalApps) * 100, 1) : 0;
        fputcsv($out, ['Placed Students', $placed, $pRate . '% Placement Rate']);
        fputcsv($out, ['Active Industry Partners', $dashboardDB->getCount('company') . ' Companies', $dashboardDB->getActiveDriveCount() . ' Active Drives']);
        fputcsv($out, ['Industry Projects', $dashboardDB->getCount('projects') . ' Projects', $dashboardDB->getSupervisorReviewProjectCount() . ' In Supervisor Review']);
        
        $totComp = $dashboardDB->getCount('complain');
        $resComp = $dashboardDB->getResolvedComplaintsCount();
        $rRate   = ($totComp > 0) ? round(($resComp / $totComp) * 100, 1) : 100;
        fputcsv($out, ['Grievance Resolution', $rRate . '% Resolved', $dashboardDB->getOpenComplaintsCount() . ' Open in Queue']);
        fputcsv($out, []);
        fputcsv($out, ['TOP RECRUITING COMPANIES']);
        fputcsv($out, ['Company Name', 'Industry', 'Vacancies', 'Applications', 'Hired']);
        $comps = $dashboardDB->getCompanyBenchmarks();
        foreach ($comps as $c) {
            fputcsv($out, [$c['Name'], $c['companytype'], $c['vacancies_count'], $c['applications_count'], $c['hired_count']]);
        }
        fputcsv($out, []);
        fputcsv($out, ['PARTNER UNIVERSITIES BENCHMARK']);
        fputcsv($out, ['University', 'Location', 'Enrolled Students']);
        $unis = $dashboardDB->getUniversityBenchmarks();
        foreach ($unis as $u) {
            fputcsv($out, [$u['University'], $u['Location'], $u['student_count']]);
        }
    }

    fclose($out);
    exit();
}

// Executive KPI Summary Calculations
$placedStudentsCount = $dashboardDB->getPlacedStudentsCount();
$totalApplications   = $dashboardDB->getCount('internship_applications');
$placementRate       = ($totalApplications > 0) ? round(($placedStudentsCount / $totalApplications) * 100, 1) : 0;

$activeCompaniesCount = $dashboardDB->getCount('company');
$activeDrivesCount    = $dashboardDB->getActiveDriveCount();

$totalProjectsCount  = $dashboardDB->getCount('projects');
$reviewProjectsCount = $dashboardDB->getSupervisorReviewProjectCount();

$totalComplaintsCount = $dashboardDB->getCount('complain');
$resolvedComplaints   = $dashboardDB->getResolvedComplaintsCount();
$openComplaintsCount  = $dashboardDB->getOpenComplaintsCount();
$resolutionRate       = ($totalComplaintsCount > 0) ? round(($resolvedComplaints / $totalComplaintsCount) * 100, 1) : 100;

// Deep-Dive Benchmark Datasets
$universityBenchmarks = $dashboardDB->getUniversityBenchmarks();
$companyBenchmarks    = $dashboardDB->getCompanyBenchmarks();
?>
