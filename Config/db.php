<?php 
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'skillbridge_db';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try{
         $conn = new mysqli($host, $user, $password, $dbname);
         if($conn->connect_error){
            die("Connection failed: " . $conn->connect_error);
         }
    }
    catch(Exception $e){
        echo "Connection failed: " . $e->getMessage();
    }
?>