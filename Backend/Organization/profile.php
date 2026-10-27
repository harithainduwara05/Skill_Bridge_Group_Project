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

$flash  = null;
$errors = [];   // field name => error message (shown under each field)

/**
 * Website / social link check. "mydb.org" is accepted and saved as "https://mydb.org".
 * $sites = allowed domains for a social link (e.g. ['linkedin.com']); empty = any website.
 * Returns the cleaned link, or null if it is not a valid link.
 */
function profileCleanUrl(string $url, array $sites = []): ?string
{
    if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
    if (strlen($url) > 200 || !filter_var($url, FILTER_VALIDATE_URL)) return null;

    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    if (strpos($host, '.') === false) return null;          // needs a domain like ".com"
    if ($sites) {
        $host = preg_replace('/^(www\.|m\.)/', '', $host);
        if (!in_array($host, $sites, true)) return null;    // e.g. a LinkedIn box must have a linkedin.com link
    }
    return $url;
}

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

    // ---- Validation ----
    // Name, Industry, Contact Number, Website and Address are required (they are required
    // when the organization registers too, and the DB columns are NOT NULL).
    $len = mb_strlen($name);
    if ($len === 0)                    $errors['org_name'] = 'Organization name is required.';
    elseif ($len < 2 || $len > 100)    $errors['org_name'] = 'Name must be 2 to 100 characters.';

    $len = mb_strlen($orgtype);
    if ($len === 0)                    $errors['industry'] = 'Industry is required.';
    elseif ($len < 2 || $len > 100)    $errors['industry'] = 'Industry must be 2 to 100 characters.';

    // Sri Lankan number: 0771234567 or +94771234567 (spaces and dashes are allowed)
    $phoneDigits = preg_replace('/[\s-]/', '', $contactNumber);
    if ($contactNumber === '')                                  $errors['contact_number'] = 'Contact number is required.';
    elseif (!preg_match('/^(0\d{9}|\+94\d{9})$/', $phoneDigits)) $errors['contact_number'] = 'Enter a valid number, e.g. 0771234567 or +94 77 123 4567.';
    else                                                        $contactNumber = $phoneDigits;   // saved without spaces

    if ($website === '') {
        $errors['website'] = 'Website URL is required.';
    } elseif (($clean = profileCleanUrl($website)) === null) {
        $errors['website'] = 'Enter a valid website, e.g. https://example.com';
    } else {
        $website = $clean;
    }

    $len = mb_strlen($location);
    if ($len === 0)                    $errors['address'] = 'Address is required.';
    elseif ($len < 5 || $len > 100)    $errors['address'] = 'Address must be 5 to 100 characters.';

    if (mb_strlen($about) > 1000)      $errors['about'] = 'About can have up to 1000 characters.';

    // Social links are optional, but a filled one must be a link to that site
    $socialSites = [
        'linkedin' => [['linkedin.com'], 'LinkedIn', 'https://linkedin.com/company/your-org'],
        'twitter'  => [['twitter.com', 'x.com'], 'Twitter / X', 'https://x.com/your-org'],
        'facebook' => [['facebook.com', 'fb.com'], 'Facebook', 'https://facebook.com/your-org'],
    ];
    $links = ['linkedin' => $linkedin, 'twitter' => $twitter, 'facebook' => $facebook];
    foreach ($socialSites as $field => [$sites, $label, $example]) {
        if ($links[$field] === '') continue;
        $clean = profileCleanUrl($links[$field], $sites);
        if ($clean === null) $errors[$field] = 'Enter a ' . $label . ' link, e.g. ' . $example;
        else                 $links[$field] = $clean;
    }
    $linkedin = $links['linkedin'];
    $twitter  = $links['twitter'];
    $facebook = $links['facebook'];

    if (!empty($errors)) {
        $flash = ['type' => 'error', 'message' => 'Please fix the highlighted fields. Nothing was saved.'];
    }

    // ---- Logo upload (optional) ----
    $logoPath = null;
    $logoError = $_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE;
    if (empty($flash) && $logoError !== UPLOAD_ERR_NO_FILE && $logoError !== UPLOAD_ERR_OK) {
        // the file was bigger than PHP allows, or the upload broke
        $flash = ['type' => 'error', 'message' => 'Logo upload failed. Use a JPG, PNG, GIF or WEBP image up to 2MB.'];
    }
    if (empty($flash) && !empty($_FILES['logo']['name']) && $logoError === UPLOAD_ERR_OK) {
        // the real type of the file is checked (not only its name)
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $maxSizeBytes = 2 * 1024 * 1024; // 2MB

        $fileType = mime_content_type($_FILES['logo']['tmp_name']);
        $fileSize = $_FILES['logo']['size'];

        if (!isset($allowedTypes[$fileType])) {
            $flash = ['type' => 'error', 'message' => 'Logo must be a JPG, PNG, GIF or WEBP image.'];
        } elseif ($fileSize > $maxSizeBytes) {
            $flash = ['type' => 'error', 'message' => 'Logo file is too large (max 2MB).'];
        } else {
            $ext = $allowedTypes[$fileType];   // extension from the real type, so "virus.php" renamed to .png can't be saved as .php
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

// Not saved because of an error -> show what was typed, so it can be fixed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)) {
    $org = array_merge($org ?? [], [
        'Name' => $_POST['org_name'] ?? '', 'orgtype' => $_POST['industry'] ?? '',
        'contactNumber' => $_POST['contact_number'] ?? '', 'website' => $_POST['website'] ?? '',
        'location' => $_POST['address'] ?? '', 'about' => $_POST['about'] ?? '',
        'linkedin' => $_POST['linkedin'] ?? '', 'twitter' => $_POST['twitter'] ?? '',
        'facebook' => $_POST['facebook'] ?? '',
    ]);
}

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