<?php
// Includes/header.php
require_once __DIR__ . '/../Session/Sessionn.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SkillBridge</title>

    <link rel="stylesheet" href="Assets/CSS/landing.css">

</head>

<body>

<header class="site-header">

    <nav class="navbar">

        <!-- Logo -->
        <a href="index.php" class="brand-logo">
            <img src="Assets/Images/logo.png" alt="SkillBridge Logo">
        </a>


        <!-- Navigation -->
        <ul class="nav-links">

            <li>
                <a href="index.php" class="active">Home</a>
            </li>

            <li>
                <a href="#about">About</a>
            </li>

            <li>
                <a href="#projects">Projects</a>
            </li>

            <li>
                <a href="#internships">Internships</a>
            </li>

            <li>
                <a href="#contact">Contact Us</a>
            </li>

        </ul>


        <!-- Navigation Buttons -->
        <div class="nav-actions">

            <a href="Auth/login.php" class="login-link">
                Login
            </a>

            <a href="register.php" class="signup-link">
                Sign Up
            </a>

        </div>


        <!-- Mobile Menu Button -->
        <button class="menu-toggle" id="menuToggle">
            ☰
        </button>

    </nav>

</header>