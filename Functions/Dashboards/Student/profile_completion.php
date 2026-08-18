<?php

function calculateProfileCompletion(
    $student,
    $skills,
    $certificates,
    $projects
){

    $completion = 0;


    if(!empty($student['profile_image'])){
        $completion += 10;
    }


    if(!empty($student['Name'])){
        $completion += 10;
    }


    if(!empty($student['University'])){
        $completion += 10;
    }


    if(!empty($student['degree'])){
        $completion += 10;
    }


    if(!empty($student['year'])){
        $completion += 10;
    }


    if(!empty($student['bio'])){
        $completion += 15;
    }


    if(!empty($student['github'])){
        $completion += 5;
    }


    if(!empty($student['linkedin'])){
        $completion += 5;
    }


    if(!empty($student['website'])){
        $completion += 5;
    }


    if($skills > 0){
        $completion += 10;
    }


    if($certificates > 0){
        $completion += 5;
    }


    if($projects > 0){
        $completion += 5;
    }


    return $completion;

}

?>