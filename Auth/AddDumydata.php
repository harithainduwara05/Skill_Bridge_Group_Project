<?php
    require_once "../Config/db.php";

    $fname = "Lahiru Dilshan";
    $email = "2024is086@stu.ucsc.cmb.ac.lk";
    $university = "Colombo University";
    $degree = "Bsc(IS)";
    $academicYear = "Year 2";
    $role = "student";

    $plainPassword = "1234"; 


    $hashedPassword = sha1($plainPassword); 

    try {

        $sql = "INSERT INTO User (Fname, Email, university, Degree, AcademicYear, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        

        $stmt->bind_param("sssssss", $fname, $email, $university, $degree, $academicYear, $hashedPassword, $role);
        

        if ($stmt->execute()) {
            echo "Data inserted successfully!";
        } else {
            echo "Failed to insert data.";
        }
        
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage();
    }
?>