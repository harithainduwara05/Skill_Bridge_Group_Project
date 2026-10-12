<?php
/*
| Organization backend - Profile / Settings: organization details + logo upload
| Used by: Functions/Organization/profile.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

// Projects are NOT promoted automatically any more.
// A new project starts as "Reviewing" and stays Reviewing even when the team is full;
// the organization changes it to "Active" manually from Edit Project.

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['org_name'] ?? '');
    $orgtype       = trim($_POST['industry'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $website       = trim($_POST['website'] ?? '');
    $location      = trim($_POST['address'] ?? '');
    $about         = trim($_POST['about'] ?? '');
    $linkedin      = trim($_POST['linkedin'] ?? '');
    $twitter       = trim($_POST['twitter'] ?? '');
    $facebook      = trim($_POST['facebook'] ?? '');

    // ---- Logo upload (optional) ----
    $logoPath = null;
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSizeBytes = 2 * 1024 * 1024; // 2MB

        $fileType = mime_content_type($_FILES['logo']['tmp_name']);
        $fileSize = $_FILES['logo']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $flash = ['type' => 'error', 'message' => 'Logo must be a JPG, PNG, GIF or WEBP image.'];
        } elseif ($fileSize > $maxSizeBytes) {
            $flash = ['type' => 'error', 'message' => 'Logo file is too large (max 2MB).'];
        } else {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $safeEmail = preg_replace('/[^a-zA-Z0-9]/', '_', $organization_email);
            $fileName = $safeEmail . '_' . time() . '.' . $ext;
            $destDir  = __DIR__ . '/../../Assets/Uploads/org_logos/';
            $destPath = $destDir . $fileName;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $destPath)) {
                $logoPath = 'Assets/Uploads/org_logos/' . $fileName;
            } else {
                $flash = ['type' => 'error', 'message' => 'Failed to save the uploaded logo.'];
            }
        }
    }

    // ---- Logo removal (only when no new logo was uploaded in the same submit) ----
    $removeLogo = empty($flash) && $logoPath === null && ($_POST['remove_logo'] ?? '') === '1';
    $oldLogoFile = null;
    if ($removeLogo) {
        $q = $conn->prepare("SELECT logo FROM organization WHERE Email=?");
        $q->bind_param("s", $organization_email);
        $q->execute();
        $oldLogoFile = $q->get_result()->fetch_assoc()['logo'] ?? null;
    }

    if (empty($flash)) {
        if ($logoPath !== null) {
            $sql = "UPDATE organization
                    SET Name=?, orgtype=?, contactNumber=?, website=?, location=?, about=?, linkedin=?, twitter=?, facebook=?, logo=?
                    WHERE Email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssss",
                $name, $orgtype, $contactNumber, $website, $location, $about, $linkedin, $twitter, $facebook, $logoPath, $organization_email
            );
        } else {
            $logoSql = $removeLogo ? ", logo=NULL" : "";
            $sql = "UPDATE organization
                    SET Name=?, orgtype=?, contactNumber=?, website=?, location=?, about=?, linkedin=?, twitter=?, facebook=?" . $logoSql . "
                    WHERE Email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssss",
                $name, $orgtype, $contactNumber, $website, $location, $about, $linkedin, $twitter, $facebook, $organization_email
            );
        }

        if ($stmt->execute()) {
            $_SESSION['user']['username'] = $name;
            if ($removeLogo) {
                // delete the old image file too (only inside the org_logos folder)
                if (!empty($oldLogoFile)) {
                    $logoDir  = realpath(__DIR__ . '/../../Assets/Uploads/org_logos');
                    $logoFile = realpath(__DIR__ . '/../../' . $oldLogoFile);
                    if ($logoDir && $logoFile && strpos($logoFile, $logoDir) === 0 && is_file($logoFile)) {
                        @unlink($logoFile);
                    }
                }
                $flash = ['type' => 'success', 'message' => 'Logo removed successfully.'];
            } else {
                $flash = ['type' => 'success', 'message' => 'Profile updated successfully.'];
            }
        } else {
            $flash = ['type' => 'error', 'message' => 'Update failed. Please try again.'];
        }
    }
}

// Fetch current organization data (also picks up the just-saved values)
$stmt = $conn->prepare("SELECT * FROM organization WHERE Email=?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$org = $stmt->get_result()->fetch_assoc();

// Stat cards — counted straight from the organization's real projects in the
// database (same source Manage Projects uses, which no longer pads with demo rows).
$projectRows = [];   // each: [status, assigned]
$stmt = $conn->prepare("SELECT p.status,
                               (SELECT COUNT(*) FROM student_projects sp WHERE sp.project_id = p.id) AS team_count
                        FROM projects p
                        WHERE p.organization_email = ?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
    $projectRows[] = [$r['status'], (int)$r['team_count']];
}

$totalProjects     = count($projectRows);
$completedProjects = 0;   // completed projects
$activeTeams       = 0;   // projects with a team assigned that are not completed yet
foreach ($projectRows as [$status, $assigned]) {
    if ($status === 'completed') {
        $completedProjects++;
    } elseif ($assigned > 0) {
        $activeTeams++;
    }
}