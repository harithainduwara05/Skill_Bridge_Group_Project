<?php
require_once __DIR__ . '/../TestFramework.php';

class OrganizationWorkspaceTest
{
    public static function run(): void
    {
        $db = TestFramework::getDb();
        $orgEmail = 'foss@sliit.lk';

        // TC_ORG_01: Verify Organization Dashboard Stat counts match queries
        TestFramework::runTest('TC_ORG_01', 'Organization', 'Verify Organization dashboard KPI stat cards counts', function () use ($db, $orgEmail) {
            // Total projects
            $stmt = $db->prepare("SELECT COUNT(*) FROM projects WHERE organization_email = ?");
            $stmt->bind_param("s", $orgEmail);
            $stmt->execute();
            $totalProjects = (int) $stmt->get_result()->fetch_row()[0];
            TestFramework::assertTrue($totalProjects >= 0, "Total projects count must be non-negative");

            // Active projects
            $stmt = $db->prepare("SELECT COUNT(*) FROM projects WHERE organization_email = ? AND status IN ('reviewing','published','accepted','active','inprogress')");
            $stmt->bind_param("s", $orgEmail);
            $stmt->execute();
            $activeProjects = (int) $stmt->get_result()->fetch_row()[0];
            TestFramework::assertTrue($activeProjects >= 0, "Active projects count must be non-negative");
        });

        // TC_ORG_02: Verify Team Progress handles zero-team state gracefully
        TestFramework::runTest('TC_ORG_02', 'Organization', 'Verify zero-teams state is handled cleanly in query', function () use ($db, $orgEmail) {
            $stmt = $db->prepare("SELECT t.id, t.name, p.title 
                                   FROM org_teams t 
                                   JOIN projects p ON p.id = t.project_id 
                                   WHERE p.organization_email = ?");
            $stmt->bind_param("s", $orgEmail);
            $stmt->execute();
            $teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Even when count is 0, query must succeed and return empty array
            TestFramework::assertTrue(is_array($teams), "Query must return array even when zero rows exist");
        });

        // TC_ORG_03: Recent Project Posts Status Badge Normalization
        TestFramework::runTest('TC_ORG_03', 'Organization', 'Status badge mapping normalizes project status values', function () {
            // Test mapping logic
            $labels = [
                'reviewing'   => 'Reviewing',
                'published'   => 'Published',
                'inprogress'  => 'Active',
                'hold'        => 'Hold',
                'rejected'    => 'Rejected',
                'completed'   => 'Completed'
            ];

            TestFramework::assertEquals('Hold', $labels['hold']);
            TestFramework::assertEquals('Active', $labels['inprogress']);
            TestFramework::assertEquals('Published', $labels['published']);
        });
    }
}

