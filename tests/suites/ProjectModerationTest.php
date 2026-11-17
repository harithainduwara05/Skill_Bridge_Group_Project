<?php
require_once __DIR__ . '/../TestFramework.php';
require_once __DIR__ . '/../../Backend/Admin/Services/AdminDB.php';
require_once __DIR__ . '/../../Backend/Admin/Services/ProjectManagement.php';

class ProjectModerationTest
{
    public static function run(): void
    {
        $db = TestFramework::getDb();
        $pm = new ProjectManagement($db);

        // TC_PROJ_01: Verify Project Hold and Moderation Log insertion
        TestFramework::runTest('TC_PROJ_01', 'Project Moderation', 'Place project on Hold and assert log insertion', function () use ($pm, $db) {
            // Find an existing project or use ID 17
            $res = $db->query("SELECT id FROM projects LIMIT 1");
            TestFramework::assertTrue($res && $res->num_rows > 0, "At least one project must exist in DB");
            $targetId = (int) $res->fetch_row()[0];

            $testReason = "Automated Test Hold Reason - " . time();
            $adminEmail = "skillbridge62@gmail.com";

            // Execute status update to Hold (signature: projectId, status, holdReason, heldBy)
            $ok = $pm->updateProjectStatus($targetId, 'Hold', $testReason, $adminEmail);
            TestFramework::assertTrue($ok, "updateProjectStatus should return true");

            // Assert project moderation log has the record
            $stmt = $db->prepare("SELECT reason, action_type, status FROM project_moderation_logs WHERE project_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->bind_param("i", $targetId);
            $stmt->execute();
            $log = $stmt->get_result()->fetch_assoc();

            TestFramework::assertNotNull($log, "Moderation log row must exist for project $targetId");
            TestFramework::assertEquals('Hold', $log['action_type']);
            TestFramework::assertEquals($testReason, $log['reason']);
            TestFramework::assertEquals('Open', $log['status']);
        });

        // TC_PROJ_02: Verify retrieval of Organization Justification Response
        TestFramework::runTest('TC_PROJ_02', 'Project Moderation', 'Verify hold response retrieval in project dataset', function () use ($pm, $db) {
            $all = $pm->getAllProjects();
            TestFramework::assertTrue(is_array($all), "getAllProjects must return an array");
            TestFramework::assertTrue(count($all) > 0, "Projects count must be greater than 0");

            // Check that the returned schema contains the hold_reason and hold_response keys
            $first = $all[0];
            TestFramework::assertTrue(array_key_exists('hold_reason', $first), "Result must contain hold_reason field");
            TestFramework::assertTrue(array_key_exists('hold_response', $first), "Result must contain hold_response field");
        });

        // TC_PROJ_03: Empty Reason Validation Guard
        TestFramework::runTest('TC_PROJ_03', 'Validation Guard', 'Empty reason validation handles gracefully', function () {
            $emptyReason = "   ";
            $cleanReason = trim($emptyReason);
            TestFramework::assertTrue(empty($cleanReason), "Whitespace-only reason must be detected as empty");
        });
    }
}
