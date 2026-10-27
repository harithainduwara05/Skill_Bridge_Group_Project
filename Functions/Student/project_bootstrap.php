<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/../../Session/Session.php';
require_once __DIR__ . '/../../Backend/Student/StudentProjectRepository.php';
require_role('student');

$projectUser = current_user();
$projectEmail = $projectUser['Email'] ?? $projectUser['email'] ?? '';
if (!is_string($projectEmail) || $projectEmail === '') {
    http_response_code(403);
    exit('Student session is invalid.');
}
$projectRepo = new StudentProjectRepository($conn);
if (empty($_SESSION['student_project_csrf'])) {
    $_SESSION['student_project_csrf'] = bin2hex(random_bytes(32));
}
$projectCsrf = $_SESSION['student_project_csrf'];

function projectEsc(?string $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function projectToast(): ?array
{
    $toast = $_SESSION['student_project_toast'] ?? null;
    unset($_SESSION['student_project_toast']);
    return $toast;
}

function projectSetToast(string $message, bool $error = false): void
{
    $_SESSION['student_project_toast'] = ['message' => $message, 'error' => $error];
}

function projectRenderToast(?array $toast): void
{
    if (!$toast) return;
    $kind = !empty($toast['error']) ? 'error' : 'success';
    echo '<div class="project-toast ' . $kind . '" role="status"><span>' . projectEsc($toast['message']) . '</span><button type="button" aria-label="Dismiss" onclick="this.parentElement.remove()">&times;</button></div>';
    echo '<script>setTimeout(() => document.querySelectorAll(".project-toast").forEach(el => el.remove()), ' . ($kind === 'error' ? '7000' : '4500') . ');</script>';
}

function projectValidId($value): int
{
    return (int) (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
}