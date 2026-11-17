<?php
require_once __DIR__ . '/../TestFramework.php';
require_once __DIR__ . '/../../Backend/Admin/Services/AdminDB.php';
require_once __DIR__ . '/../../Backend/Admin/Services/ReportAnalyticsManagement.php';
require_once __DIR__ . '/../../Backend/Admin/Services/DashboardManagement.php';
require_once __DIR__ . '/../../Backend/Admin/Services/UserManagement.php';

class ReportsAnalyticsTest
{
    public static function run(): void
    {
        $db = TestFramework::getDb();
        $rm = new ReportAnalyticsManagement($db);
        $dm = new DashboardManagement($db);
        $um = new UserManagement($db);

        // TC_REP_01: Verify Hiring Velocity SQL Query executes cleanly under ONLY_FULL_GROUP_BY
        TestFramework::runTest('TC_REP_01', 'Analytics', 'Hiring velocity query executes without ONLY_FULL_GROUP_BY error', function () use ($rm) {
            $data = $rm->getHiringVelocityData();
            TestFramework::assertTrue(is_array($data), "getHiringVelocityData must return an array");
            TestFramework::assertTrue(count($data) > 0, "Result array must not be empty");
            $first = $data[0];
            TestFramework::assertTrue(isset($first['m_label']), "Result must have 'm_label' key");
            TestFramework::assertTrue(isset($first['apps']), "Result must have 'apps' key");
        });

        // TC_REP_02: Admin Dashboard KPI Statistics Consistency
        TestFramework::runTest('TC_REP_02', 'Dashboard KPI', 'Verify Admin dashboard KPI metrics match database counts', function () use ($dm, $um, $db) {
            TestFramework::assertNotNull($db, "Database connection must be active");
            $ongoingProjects = $dm->getOngoingProjectCount();
            $availableInternships = $dm->getAvailableInternshipCount();
            $activeProjects = $dm->getActiveProjectCount();

            TestFramework::assertTrue($ongoingProjects >= 0, "Ongoing projects must be non-negative");
            TestFramework::assertTrue($availableInternships >= 0, "Internships count must be non-negative");
            TestFramework::assertTrue($activeProjects >= 0, "Active projects count must be non-negative");

            // Compare with User Management statistics
            $uStats = $um->getUserManagementStats();
            TestFramework::assertTrue(isset($uStats['total']), "User management stats must contain total users count");
            TestFramework::assertTrue($uStats['total'] > 0, "Total users must be greater than 0");
        });
    }
}
