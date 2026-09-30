<?php
/**
 * SkillBridge - Project Management Backend Controller
 * Handles project queries, filters, approvals, and metrics
 */

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/AdminBackend.php';

if (!isset($adminDB) || !($adminDB instanceof AdminDB)) {
    $adminDB = new AdminDB($conn);
}

// Any future backend queries, approvals, or stat updates for projects can be handled here
