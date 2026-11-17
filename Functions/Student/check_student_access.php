<?php
function getStudentYearNumber($yearText){
    preg_match('/\d+/', $yearText, $matches);
    return intval($matches[0] ?? 0);
}

if(!function_exists('canApplyInternship')){

    function canApplyInternship($email){
        global $conn;

        $sql="
        SELECT year
        FROM student
        WHERE Email=?
        ";

        $stmt=$conn->prepare($sql);
        $stmt->bind_param("s",$email);
        $stmt->execute();


        $student=$stmt
            ->get_result()
            ->fetch_assoc();

        if(!$student){
            return false;
        }

        $year = getStudentYearNumber(
            $student['year']
        );

        return $year >= 3;
    }
}

if(!function_exists('validateStudentYear')){

    function validateStudentYear($email){

        global $conn;
        $sql="
        SELECT year
        FROM student
        WHERE Email=?
        ";

        $stmt=$conn->prepare($sql);

        $stmt->bind_param(
            "s",$email);

        $stmt->execute();
        $student=$stmt
            ->get_result()
            ->fetch_assoc();

        if(!$student){
            return false;
        }

        $year = getStudentYearNumber(
            $student['year']
        );

        return ($year >= 1 && $year <= 4);


    }
}
?>