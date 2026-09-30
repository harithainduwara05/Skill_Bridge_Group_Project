<?php
/**
 * SkillBridge - Reports & Performance Analytics Backend Controller
 * Handles reporting metrics, period filters, and data exports
 */

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/AdminBackend.php';

if (!isset($adminDB) || !($adminDB instanceof AdminDB)) {
    $adminDB = new AdminDB($conn);
}

// Any reporting calculations, charts data feeds, or exports can be processed here
