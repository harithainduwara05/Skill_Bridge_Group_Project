<?php
require_once __DIR__ . '/../Config/db.php';
$p = $conn->query('SELECT id, title, organization_email, category_id, status FROM projects WHERE id = 17')->fetch_assoc();
print_r($p);

$o = $conn->query("SELECT Email, Name FROM organization WHERE Email = '{$p['organization_email']}'")->fetch_assoc();
echo "Organization check:\n";
print_r($o);

$c = $conn->query("SELECT category_id, category_name FROM project_categories WHERE category_id = '{$p['category_id']}'")->fetch_assoc();
echo "Category check:\n";
print_r($c);

require_once __DIR__ . '/../Backend/Admin/Services/AdminDB.php';
require_once __DIR__ . '/../Backend/Admin/Services/ProjectManagement.php';
$pm = new ProjectManagement($conn);
$all = $pm->getAllProjects();
$found = false;
foreach ($all as $item) {
    if ($item['id'] == 17) {
        $found = true;
        echo "FOUND in getAllProjects():\n";
        print_r($item);
        break;
    }
}
if (!$found) {
    echo "NOT FOUND in getAllProjects()!\n";
}

