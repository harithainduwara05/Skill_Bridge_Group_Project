<?php
require_once __DIR__ . '/../TestFramework.php';
require_once __DIR__ . '/../../Backend/Admin/Services/AdminDB.php';
require_once __DIR__ . '/../../Backend/Admin/Services/UserManagement.php';

class UserManagementTest
{
    public static function run(): void
    {
        $db = TestFramework::getDb();
        $um = new UserManagement($db);

        // TC_USER_01: Verify valid University domains exist in university_faculties
        TestFramework::runTest('TC_USER_01', 'User Management', 'Verify university faculties domains exist for student FK', function () use ($db) {
            TestFramework::assertNotNull($db, "Database connection must be active");
            $res = $db->query("SELECT domain, University, faculty FROM university_faculties WHERE domain IS NOT NULL AND domain != '' LIMIT 5");
            TestFramework::assertTrue($res && $res->num_rows > 0, "university_faculties table must contain valid domains");
            $row = $res->fetch_assoc();
            TestFramework::assertNotEmpty($row['domain'], "Domain string must not be empty");
            TestFramework::assertNotEmpty($row['University'], "University name must not be empty");
        });

        // TC_USER_02: Foreign Key Constraint Check on Student Domain
        TestFramework::runTest('TC_USER_02', 'Database Integrity', 'Foreign key constraint definition verified on student.domain', function () use ($db) {
            TestFramework::assertNotNull($db, "Database connection must be active");
            $invalidDomain = "nonexistent_domain_" . uniqid() . ".xyz";
            
            // Check if domain exists in university_faculties
            $stmt = $db->prepare("SELECT COUNT(*) FROM university_faculties WHERE domain = ?");
            $stmt->bind_param("s", $invalidDomain);
            $stmt->execute();
            $exists = (int)$stmt->get_result()->fetch_row()[0];
            TestFramework::assertEquals(0, $exists, "Invalid domain must not exist in university_faculties");

            // Verify foreign key definition on student table
            $fkRes = $db->query("SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                                 FROM information_schema.KEY_COLUMN_USAGE 
                                 WHERE TABLE_NAME = 'student' AND COLUMN_NAME = 'domain' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
            TestFramework::assertTrue($fkRes && $fkRes->num_rows > 0, "Foreign key constraint on student.domain must be defined");
            $fkRow = $fkRes->fetch_assoc();
            TestFramework::assertEquals('university_faculties', $fkRow['REFERENCED_TABLE_NAME']);
            TestFramework::assertEquals('domain', $fkRow['REFERENCED_COLUMN_NAME']);
        });

        // TC_USER_03: Instant Client-Side Search Data Preload Verification
        TestFramework::runTest('TC_USER_03', 'User Management', 'Preloaded users dataset contains all required search attributes', function () use ($um) {
            $users = $um->getUsers();
            TestFramework::assertTrue(is_array($users), "getUsers must return array");
            TestFramework::assertTrue(count($users) > 0, "Total users count must be greater than 0");

            $sample = $users[0];
            TestFramework::assertTrue(isset($sample['user_name']) || isset($sample['name']) || isset($sample['Name']), "User record must have name");
            TestFramework::assertTrue(isset($sample['email']) || isset($sample['Email']), "User record must have email");
            TestFramework::assertTrue(isset($sample['role']), "User record must have role");
            TestFramework::assertTrue(isset($sample['status']), "User record must have status");
        });
    }
}
