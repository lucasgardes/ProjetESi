<?php 
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  $logged = true;
} else {
  $logged = false;
}
?>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../CSS/header.css">
<div class="w3-top">
    <div class="w3-bar w3-green w3-card w3-left-align w3-large">
      <a class="w3-bar-item w3-button w3-padding-large w3-white">Home</a>
      <a href="#contact" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">Contact</a>
  
      <?php if (!$logged) : ?>
      <div class="w3-right w3-hide-small" id="login-signup-container">
          <a href="login.php" class="w3-bar-item w3-button w3-padding-large w3-hover-white">Connexion</a>
          <a href="signup.php" class="w3-bar-item w3-button w3-padding-large w3-hover-white">Inscription</a>
      </div>
      <?php else : ?>
      <div id="user-dropdown" class="dropdown">
          <button class="dropbtn"><i class="fa fa-user"></i></button>
          <div class="dropdown-content">
              <a href="#profile">Profil</a>
              <a href="#settings">Paramètres</a>
              <!-- <a href="#routes" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">Routes</a>
              <a href="#cyclists" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">Cyclists</a>
              <a href="#statistics" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">Statistics</a> -->
              <a href="#logout">Déconnexion</a>
          </div>
      </div>
      <?php endif; ?>
    </div>
</div>