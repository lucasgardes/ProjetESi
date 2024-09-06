<?php 
include 'pdo.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  $logged = true;
} else {
  $logged = false;
}

$admin = false;
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT c.admin FROM client c WHERE c.id = ?");
$stmt->execute([$userId]);
$clientAdminInfo = $stmt->fetch();
if ($clientAdminInfo['admin']) {
  $admin = true;
}

$bicycleAssociated = false;
$stmt = $pdo->prepare("SELECT b.id FROM client c LEFT JOIN bicycles b ON b.client_id = c.id WHERE c.id = ?");
$stmt->execute([$userId]);
$bicycleId = $stmt->fetch();
if (isset($bicycleId['id']) && !is_null($bicycleId['id'])) {
  $bicycleAssociated = true;
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../CSS/header.css">

<!-- Menu Bootstrap -->
<nav class="navbar navbar-expand-lg navbar-light navbar-gradient">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <img
        class="logo"
        src="../img/logo.webp"
        alt="Logo" />
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav ms-auto">
        
        <?php if (!$logged) : ?>
        <li class="nav-item">
          <a class="nav-link" href="login.php">Connexion</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="signup.php">Inscription</a>
        </li>
        <?php else : ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-user"></i> Mon Compte
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
            <li><a class="dropdown-item" href="edit_profile.php">Profil</a></li>
            <?php if ($bicycleAssociated) : ?>
              <li><a class="dropdown-item" href="dashboard.php">Historique des Itinéraires</a></li>
              <li><a class="dropdown-item" href="user_dashboard.php">Choisir un Itinéraire</a></li>
            <?php endif; ?>
            <?php if ($admin) : ?>
              <li><a class="dropdown-item" href="manage_bicycles.php">Gestion des Vélos</a></li>
              <li><a class="dropdown-item" href="analysis_and_reports.php">Statistiques des Vélos</a></li>
              <li><a class="dropdown-item" href="manage_users.php">Gestion des Utilisateurs</a></li>
            <?php endif; ?>
            <li><a class="dropdown-item" href="logout.php">Déconnexion</a></li>
          </ul>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Inclure Bootstrap JS et Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
