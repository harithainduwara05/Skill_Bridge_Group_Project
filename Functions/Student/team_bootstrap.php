<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/../../Session/Session.php';
require_once __DIR__ . '/../../Backend/Student/StudentTeamRepository.php';

require_role('student');
$user = current_user();
$email = $user['Email'] ?? $user['email'] ?? null;
if (!is_string($email) || $email === '') {
    http_response_code(403);
    exit('Student session is unavailable.');
}
$teamRepository = new StudentTeamRepository($conn);

function teamEsc(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function teamInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    return strtoupper(substr($parts[0] ?? 'T', 0, 1) . substr($parts[1] ?? '', 0, 1));
}

function teamStatus(string $value): string
{
    return ucwords(str_replace(['_', '-'], ' ', $value));
}

function teamDate(?string $value): string
{
    return $value ? date('d M Y', strtotime($value)) : 'Not set';
}
