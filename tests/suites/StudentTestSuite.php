<?php
require_once __DIR__ . '/../TestFramework.php';
require_once __DIR__ . '/../../Backend/Student/StudentBackend.php';
require_once __DIR__ . '/../../Backend/Student/StudentProjectRepository.php';

class StudentTestSuite
{
    public static function run(): void
    {
        $db = TestFramework::getDb();
        $studentBackend = new StudentManager($db);
        $projectRepo = new StudentProjectRepository($db);
        $testStudentEmail = '2024is001@stu.ucsc.cmb.ac.lk';

        // TC_STUD_01: Verify Student Profile Retrieval
        TestFramework::runTest('TC_STUD_01', 'Student Actor', 'Retrieve student profile details and university enrollment', function () use ($studentBackend, $testStudentEmail) {
            $student = $studentBackend->getStudent($testStudentEmail);
            TestFramework::assertNotNull($student, "Student profile must exist for $testStudentEmail");
            TestFramework::assertNotEmpty($student['Name'], "Student name must not be empty");
            TestFramework::assertNotEmpty($student['University'], "Student university must not be empty");
            TestFramework::assertEquals($testStudentEmail, strtolower($student['Email']));
        });

        // TC_STUD_02: Browse Available Projects (Students can only see Public and Non-Hold/Non-Rejected projects)
        TestFramework::runTest('TC_STUD_02', 'Student Actor', 'Verify student project browsing excludes Hold/Rejected projects', function () use ($projectRepo) {
            $projects = $projectRepo->list([], 1, 10);
            TestFramework::assertTrue(is_array($projects), "Project list must be an array");
            
            // Validate that every project shown to students is NOT hold, rejected, or suspended
            foreach ($projects as $proj) {
                $status = strtolower($proj['status'] ?? '');
                TestFramework::assertFalse(
                    in_array($status, ['hold', 'rejected', 'suspended']),
                    "Student must not be shown projects with status: " . $status
                );
            }
        });

        // TC_STUD_03: Check Student Saved Internships / Applications Query
        TestFramework::runTest('TC_STUD_03', 'Student Actor', 'Verify student internship applications query execution', function () use ($studentBackend, $testStudentEmail) {
            $apps = $studentBackend->getInternshipApplications($testStudentEmail);
            TestFramework::assertTrue(is_array($apps), "getInternshipApplications must return an array");

            $saved = $studentBackend->getSavedInternships($testStudentEmail);
            TestFramework::assertTrue(is_array($saved), "getSavedInternships must return an array");
        });

        // TC_STUD_04: Prevent Duplicate Project Applications
        TestFramework::runTest('TC_STUD_04', 'Student Actor', 'Verify duplicate proposal application check guard', function () use ($projectRepo, $testStudentEmail) {
            // Check method alreadyApplied returns boolean
            $applied = $projectRepo->alreadyApplied($testStudentEmail, 999999);
            TestFramework::assertFalse($applied, "Student should not have applied to non-existent project 999999");
        });
    }
}
