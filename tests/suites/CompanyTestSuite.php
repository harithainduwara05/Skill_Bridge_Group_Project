<?php
require_once __DIR__ . '/../TestFramework.php';
require_once __DIR__ . '/../../Backend/CompanyBackend.php';

class CompanyTestSuite
{
    public static function run(): void
    {
        $db = TestFramework::getDb();
        $companyMgr = new CompanyManager($db);
        $testCompanyEmail = 'careers@virtusa.com';

        // TC_COMP_01: Verify Company Profile Retrieval
        TestFramework::runTest('TC_COMP_01', 'Company Actor', 'Retrieve verified company profile and details', function () use ($companyMgr, $testCompanyEmail) {
            $company = $companyMgr->getCompany($testCompanyEmail);
            TestFramework::assertNotNull($company, "Company record must exist for $testCompanyEmail");
            TestFramework::assertEquals($testCompanyEmail, strtolower($company['Email']));
            TestFramework::assertNotEmpty($company['Name'], "Company name must not be empty");
            TestFramework::assertEquals('Verify', $company['Status']);
        });

        // TC_COMP_02: Company Profile Data Integrity
        TestFramework::runTest('TC_COMP_02', 'Company Actor', 'Verify Company details integrity in database', function () use ($companyMgr, $testCompanyEmail) {
            $company = $companyMgr->getCompany($testCompanyEmail);
            TestFramework::assertNotEmpty($company['companytype'], "Company type must be set");
            TestFramework::assertNotEmpty($company['location'], "Company location must be set");
            TestFramework::assertNotEmpty($company['contactPersonName'], "Contact person must be set");
        });

        // TC_COMP_03: Retrieve Company Internships by Email
        TestFramework::runTest('TC_COMP_03', 'Company Actor', 'Verify retrieval of company internship listings', function () use ($db, $testCompanyEmail) {
            $stmt = $db->prepare("SELECT id, title, deadline_date FROM internships WHERE company_email = ?");
            $stmt->bind_param("s", $testCompanyEmail);
            $stmt->execute();
            $internships = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            TestFramework::assertTrue(is_array($internships), "Internships must return an array");
        });

        // TC_COMP_04: Retrieve Received Candidate Applications for Company
        TestFramework::runTest('TC_COMP_04', 'Company Actor', 'Verify retrieval of applicant submissions for company', function () use ($db, $testCompanyEmail) {
            $stmt = $db->prepare("SELECT a.application_id, a.Email, a.status 
                                   FROM internship_applications a 
                                   JOIN internships i ON i.id = a.internship_id 
                                   WHERE i.company_email = ?");
            $stmt->bind_param("s", $testCompanyEmail);
            $stmt->execute();
            $apps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            TestFramework::assertTrue(is_array($apps), "Applicant submissions must return an array");
        });
    }
}
