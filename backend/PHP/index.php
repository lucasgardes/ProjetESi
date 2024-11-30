<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - Accueil</title>
    <link rel="stylesheet" href="../CSS/index.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Bienvenue sur le Backoffice</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="manage_users.php">Gestion des Utilisateurs</a></li>
                    <li><a href="manage_bicycles.php">Gestion des Vélos</a></li>
                    <li><a href="analysis_and_reports.php">Rapports & Analyses</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <section>
                <h2>Dashboard</h2>
                <p>Utilisez le menu pour naviguer dans les différentes sections de l'administration.</p>
            </section>
        </main>
        <footer>
            <p>&copy; 2024 Mon Backoffice</p>
        </footer>
    </div>
</body>
</html>
