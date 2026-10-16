<?php
require_once __DIR__ . '/loader.php';

// Object Initialization
$projectManagement = new ProjectManagement($conn);
$adminDB           = $projectManagement;

// Card Statistics
$activeProjects    = $projectManagement->getActiveProjectCount();
$totalProjects     = $projectManagement->getCount('projects');
$reviewingProjects = $projectManagement->getCountWhere('Project','status','reviewing');
$completedProjects = $projectManagement->getCountWhere('Project','status','completed');

// Fetch Data for Projects Table and Filters
$projectsList      = $projectManagement->getAllProjects();
$categoriesList    = $projectManagement->getDistinctCategories();