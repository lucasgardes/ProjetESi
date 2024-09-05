<?php 
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  $logged = true;
} else {
  $logged = false;
}
?>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../CSS/header.css">

<!-- Menu Bootstrap -->
<nav class="navbar navbar-expand-lg navbar-light navbar-gradient">
  <a class="navbar-brand" href="index.php">MonSite</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNavDropdown">
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="contact.php">Contact</a>
      </li>
      
      <?php if (!$logged) : ?>
      <li class="nav-item">
        <a class="nav-link" href="login.php">Connexion</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="signup.php">Inscription</a>
      </li>
      <?php else : ?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-user"></i> Mon Compte
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" href="profile.php">Profil</a>
          <a class="dropdown-item" href="settings.php">Paramètres</a>
          <a class="dropdown-item" href="logout.php">Déconnexion</a>
        </div>
      </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<!-- Inclure Bootstrap JS et Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
