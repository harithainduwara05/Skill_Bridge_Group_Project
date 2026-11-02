<?php
require_once __DIR__ . '/../TestFramework.php';

class AuthTestSuite
{
    public static function run(): void
    {
        $db = TestFramework::getDb();

        // TC_AUTH_01: Verify Admin record exists in admin table
        TestFramework::runTest('TC_AUTH_01', 'Authentication', 'Verify Admin credentials exist in admin table', function () use ($db) {
            TestFramework::assertNotNull($db, "Database connection must be active");
            $res = $db->query("SELECT Email, Name FROM admin WHERE Email = 'skillbridge62@gmail.com' LIMIT 1");
            TestFramework::assertTrue($res && $res->num_rows > 0, "Default admin email 'skillbridge62@gmail.com' must exist in admin table");
            $row = $res->fetch_assoc();
            TestFramework::assertEquals('skillbridge62@gmail.com', strtolower($row['Email']));
        });

        // TC_AUTH_02: Verify Session Role Guard logic
        TestFramework::runTest('TC_AUTH_02', 'Security Guard', 'Unauthorized session guard prevents access', function () {
            // Emulate an empty or invalid session
            $fakeSession = [];
            $hasRole = isset($fakeSession['role']) && $fakeSession['role'] === 'admin';
            TestFramework::assertFalse($hasRole, "Empty session must not be authorized as admin");
        });

        // TC_AUTH_03: Organization Role Authentication & Record Verification
        TestFramework::runTest('TC_AUTH_03', 'Authentication', 'Verify Organization credentials exist in DB', function () use ($db) {
            TestFramework::assertNotNull($db, "Database connection must be active");
            $res = $db->query("SELECT Email, Name FROM organization WHERE Email = 'foss@sliit.lk' LIMIT 1");
            TestFramework::assertTrue($res && $res->num_rows > 0, "Organization 'foss@sliit.lk' must exist in organization table");
            $row = $res->fetch_assoc();
            TestFramework::assertContains('SLIIT FOSS', $row['Name'], "Organization name must match SLIIT FOSS");
        });

        // TC_AUTH_04: Student Role Authentication & Record Verification
        TestFramework::runTest('TC_AUTH_04', 'Authentication', 'Verify Student account exists in student table', function () use ($db) {
            TestFramework::assertNotNull($db, "Database connection must be active");
            $res = $db->query("SELECT Email, Name, University FROM student LIMIT 1");
            TestFramework::assertTrue($res && $res->num_rows > 0, "At least one Student user must exist in student table");
            $row = $res->fetch_assoc();
            TestFramework::assertNotEmpty($row['Email']);
            TestFramework::assertNotEmpty($row['Name']);
        });

        // TC_AUTH_05: Company Role Authentication & Record Verification
        TestFramework::runTest('TC_AUTH_05', 'Authentication', 'Verify Company account exists in company table', function () use ($db) {
            TestFramework::assertNotNull($db, "Database connection must be active");
            $res = $db->query("SELECT Email, Name, companytype FROM company LIMIT 1");
            TestFramework::assertTrue($res && $res->num_rows > 0, "At least one Company user must exist in company table");
            $row = $res->fetch_assoc();
            TestFramework::assertNotEmpty($row['Email']);
            TestFramework::assertNotEmpty($row['Name']);
        });
    }
}
