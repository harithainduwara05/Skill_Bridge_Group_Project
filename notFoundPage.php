<?php
http_response_code(404);
require_once __DIR__.'/Session/Session.php';
$role=current_user()['role'] ??null;
?>
<html>
    <head>
        <link rel="stylesheet" href="/Skill_Bridge_Group_Project/Assets/CSS/dashboard.css" />
    </head>
    <body class='notfound'>
        <h1>404</h1>
        <h2>Oops! Not Found</h2>
        <p>The Page you're looking for doesn't exits or has been moved.</p>
        <div class="nfButton">
            <button onclick="task()">Goto Dashbord</button>
        </div>
        
    </body>
    <script>
        const role="<?php echo $role ?? '';?>";
        const task = () => {
            switch(role){
                case 'admin':
                    window.location.href='/Skill_Bridge_Group_Project/Functions/Admin/dashboard.php';
                    break;
                case 'organization':
                    window.location.href='/Skill_Bridge_Group_Project/Functions/Organization/dashboard.php';
                    break;
                case 'company':
                    window.location.href='/Skill_Bridge_Group_Project/Functions/Company/dashboard.php';
                    break;
                case 'student':
                    window.location.href='/Skill_Bridge_Group_Project/Functions/Student/dashboard.php';
                    break;
                default:
                    window.location.href='/Skill_Bridge_Group_Project/index.php';
                    break;
            }

        };
    </script>