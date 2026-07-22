<?php 
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'Skill_Bridge_DB';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try{
         $conn = new mysqli($host, $user, $password, $dbname);
         if($conn->connect_error){
            die("Connection failed: " . $conn->connect_error);
         }
         else{
            echo "Connected successfully";
         }
    }
    catch(Exception $e){
        echo "Connection failed: " . $e->getMessage();
    }
?>