<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['backend_user_id'])) {
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
    <?php include 'header.php'; ?>
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
