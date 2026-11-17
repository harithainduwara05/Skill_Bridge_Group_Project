<?php
/**
 * SkillBridge Automated Test Runner Entrypoint
 * Command line runner for all system test suites across 4 Actors:
 * Admin, Organization, Student, Company.
 * Usage: php tests/run_tests.php
 */

require_once __DIR__ . '/TestFramework.php';
require_once __DIR__ . '/suites/AuthTestSuite.php';
require_once __DIR__ . '/suites/ProjectModerationTest.php';
require_once __DIR__ . '/suites/UserManagementTest.php';
require_once __DIR__ . '/suites/ReportsAnalyticsTest.php';
require_once __DIR__ . '/suites/OrganizationWorkspaceTest.php';
require_once __DIR__ . '/suites/StudentTestSuite.php';
require_once __DIR__ . '/suites/CompanyTestSuite.php';

// Disable default PHP error output to prevent cluttering runner logs
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

echo "\n";
echo "=====================================================================================\n";
echo "           SKILLBRIDGE PLATFORM - AUTOMATED TEST SUITE RUNNER                        \n";
echo "=====================================================================================\n";
echo " Target Database: MySQL (Local / Cloud)          | PHP Version: " . PHP_VERSION . "\n";
echo " Actors Tested: Admin, Organization, Student, Company                                \n";
echo " Test Execution Mode: Automated Evidence Collection & Validation                     \n";
echo "-------------------------------------------------------------------------------------\n";
echo sprintf(" %-12s | %-16s | %-45s | %s\n", "TEST ID", "ACTOR / SUITE", "SCENARIO / ASSERTION", "STATUS & TIME");
echo "-------------------------------------------------------------------------------------\n";

TestFramework::init();

// Execute all test suites for all 4 Actors
AuthTestSuite::run();
ProjectModerationTest::run();
UserManagementTest::run();
ReportsAnalyticsTest::run();
OrganizationWorkspaceTest::run();
StudentTestSuite::run();
CompanyTestSuite::run();

// Produce final console summary and export HTML report
TestFramework::generateSummaryAndHtml();
