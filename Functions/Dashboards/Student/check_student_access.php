<?php

if(!function_exists('canApplyInternship')){


    function canApplyInternship($email){

        global $conn;


        $sql="
        SELECT year
        FROM student
        WHERE Email=?
        ";


        $stmt=$conn->prepare($sql);


        $stmt->bind_param(
            "s",
            $email
        );


        $stmt->execute();


        $student=$stmt
        ->get_result()
        ->fetch_assoc();



        if(!$student){

            return false;

        }



        // Extract only the digits from the string (e.g., "Year 3" -> 3 or "2024" -> 2024)
        $yearStr = preg_replace('/[^0-9]/', '', $student['year']);
        $year = intval($yearStr);

        // If year is e.g., 3, 4 or 2021 (which implies > 1st/2nd year), we could do a check.
        // For now, let's just make sure it's valid.
        return $year > 0;

    }


}

if(!function_exists('validateStudentYear')){


function validateStudentYear($email){

    global $conn;

    $sql = "
    SELECT year
    FROM student
    WHERE Email=?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "s",
        $email
    );

    $stmt->execute();


    $student = $stmt
        ->get_result()
        ->fetch_assoc();


    if(!$student){
        return false;
    }


    // Extract only the digits from the string
    $yearStr = preg_replace('/[^0-9]/', '', $student['year']);
    $year = intval($yearStr);

    // If the student exists and has a numeric year (like 2, or 2024), they are valid
    return $year > 0;

}


}
?>