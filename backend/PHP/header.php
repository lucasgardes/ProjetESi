<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Déterminer la page active pour le menu
$currentPage = basename($_SERVER['PHP_SELF']); // Obtenir le nom de la page actuelle
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice</title>
    <link rel="stylesheet" href="../CSS/index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Backoffice</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'manage_users.php' ? 'active' : '' ?>" href="manage_users.php">Gestion des Utilisateurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'manage_bicycles.php' ? 'active' : '' ?>" href="manage_bicycles.php">Gestion des Vélos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'analysis_and_reports.php' ? 'active' : '' ?>" href="analysis_and_reports.php">Rapports & Analyses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
