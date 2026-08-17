<?php


class StudentManager
{


    // Get student details
public function getStudent($email)
{
    global $conn;

    $sql="
    SELECT 
        student.*,
        user.status AS user_status

    FROM student

    INNER JOIN user

    ON student.Email = user.Email

    WHERE student.Email=?
    ";

    $stmt=$conn->prepare($sql);

    $stmt->bind_param(
        "s",
        $email
    );

    $stmt->execute();

    return $stmt
    ->get_result()
    ->fetch_assoc();
}

    // Count skills
    public function getSkillCount($email)
    {
        global $conn;
        $sql="
        SELECT COUNT(*)
        FROM skills
        WHERE Email=?
        ";

        $stmt=$conn->prepare($sql);
        $stmt->bind_param(
            "s",
            $email
        );
        $stmt->execute();
        return $stmt
        ->get_result()
        ->fetch_row()[0];

    }

    // Count certificates
    public function getCertificateCount($email)
    {
        global $conn;
        $sql="
        SELECT COUNT(*)
        FROM certificates
        WHERE Email=?
        ";

        $stmt=$conn->prepare($sql);
        $stmt->bind_param(
            "s",
            $email
        );
        $stmt->execute();
        return $stmt
        ->get_result()
        ->fetch_row()[0];

    }

    // Count projects
    public function getProjectCount($email)
    {
        global $conn;
        $sql="
        SELECT COUNT(*)
        FROM student_projects
        WHERE Email=?
        ";
        $stmt=$conn->prepare($sql);
        $stmt->bind_param(
            "s",
            $email
        );
        $stmt->execute();


        return $stmt
        ->get_result()
        ->fetch_row()[0];

    }


    // Count internship applications
    public function getApplicationCount($email)
    {
        global $conn;

        $sql="
        SELECT COUNT(*)
        FROM internship_applications
        WHERE Email=?
        ";

        $stmt=$conn->prepare($sql);
        $stmt->bind_param(
            "s",
            $email
        );

        $stmt->execute();
        return $stmt
        ->get_result()
        ->fetch_row()[0];

    }

// Get Recommended Internships
public function getRecommendedInternships()
{
    global $conn;
    $sql = "

    SELECT
        id,
        title,
        company,
        industry,
        tech_tags,
        duration,
        deadline

    FROM internships

    ORDER BY id DESC

    LIMIT 2

    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);


}

// Get skills
public function getSkills($email)
{
    global $conn;
    $sql="
    SELECT
    skill_id,
    skill_name,
    level,
    experience

    FROM skills

    WHERE Email=?

    ORDER BY skill_id DESC
    LIMIT 6
    ";

    $stmt=$conn->prepare($sql);
    $stmt->bind_param(
        "s",
        $email
    );

    $stmt->execute();
    return $stmt
    ->get_result()
    ->fetch_all(MYSQLI_ASSOC);

}

// Get Projects
public function getProjects($email)
{

    global $conn;


    $sql = "

    SELECT

    p.id,
    p.title,
    p.company AS organization,
    sp.role AS team,
    sp.progress,
    sp.status


    FROM student_projects sp


    INNER JOIN projects p

    ON sp.project_id = p.id


    WHERE sp.Email = ?


    ORDER BY sp.student_project_id DESC


    LIMIT 2

    ";


    $stmt=$conn->prepare($sql);


    $stmt->bind_param("s",$email);


    $stmt->execute();


    return $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

}

// Get student notifications
public function getNotifications($email)
{

    global $conn;

    $sql = "
    SELECT 
        title,
        message,
        type,
        created_at

    FROM notifications

    WHERE Email=?

    ORDER BY created_at DESC

    LIMIT 3
    ";


    $stmt = $conn->prepare($sql);


    $stmt->bind_param("s",$email);


    $stmt->execute();


    return $stmt
    ->get_result()
    ->fetch_all(MYSQLI_ASSOC);

}
}
?>