<!DOCTYPE html>
<html lang="en">
<head>
<title>Green City Waste Collection</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../CSS/index.css">
<script src="../JS/index.js"></script>
</head>
<body>

<!-- Navbar -->
<div class="w3-top">
  <div class="w3-bar w3-green w3-card w3-left-align w3-large">
    <a class="w3-bar-item w3-button w3-padding-large w3-white">Home</a>
    <a href="#contact" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">Contact</a>

    <?php include 'login.php';?>
    <div class="w3-right w3-hide-small" id="login-signup-container">
        <a href="login.php" class="w3-bar-item w3-button w3-padding-large w3-hover-white">Connexion</a>
        <a href="signup.php" class="w3-bar-item w3-button w3-padding-large w3-hover-white">Inscription</a>
    </div>
    <?php if ($logged) : ?>
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

<!-- Header -->
<header class="w3-container w3-green w3-center" style="padding:128px 16px">
  <h1 class="w3-margin w3-jumbo">Green City Waste Collection</h1>
  <p class="w3-xlarge">Efficient and Eco-Friendly Waste Collection</p>
  <button class="w3-button w3-black w3-padding-large w3-large w3-margin-top">Learn More</button>
</header>

<!-- First Grid -->
<div class="w3-row-padding w3-padding-64 w3-container">
  <div class="w3-content">
    <div class="w3-twothird">
      <h1>About Our Service</h1>
      <h5 class="w3-padding-32">Discover how we're transforming waste collection with our fleet of electric cargo bikes, making our city cleaner and greener.</h5>

      <p class="w3-text-grey">Our innovative approach reduces traffic congestion and emissions, providing a sustainable solution to waste management. Join us in our mission to create a more eco-friendly city.</p>
    </div>

    <div class="w3-third w3-center">
      <i class="fa fa-recycle w3-padding-64 w3-text-green"></i>
    </div>
  </div>
</div>

<!-- Second Grid -->
<div class="w3-row-padding w3-light-grey w3-padding-64 w3-container">
  <div class="w3-content">
    <div class="w3-third w3-center">
      <i class="fa fa-bicycle w3-padding-64 w3-text-green w3-margin-right"></i>
    </div>

    <div class="w3-twothird">
      <h1>Join Our Team</h1>
      <h5 class="w3-padding-32">Interested in becoming a part of our cycling team? Learn more about the benefits and how you can contribute to a greener city.</h5>

      <p class="w3-text-grey">We're always looking for motivated individuals who share our passion for environmental sustainability. Apply today and help us make a difference!</p>
    </div>
  </div>
</div>

<div class="w3-container w3-black w3-center w3-opacity w3-padding-64">
    <h1 class="w3-margin w3-xlarge">Empowering communities to sustain a cleaner, healthier environment</h1>
</div>

<!-- Footer -->
<footer class="w3-container w3-padding-64 w3-center w3-opacity">  
  <div class="w3-xlarge w3-padding-32">
    <i class="fa fa-facebook-official w3-hover-opacity"></i>
    <i class="fa fa-instagram w3-hover-opacity"></i>
    <i class="fa fa-twitter w3-hover-opacity"></i>
    <i class="fa fa-linkedin w3-hover-opacity"></i>
 </div>
</footer>

<script>
function myFunction() {
  var x = document.getElementById("navDemo");
  if (x.className.indexOf("w3-show") == -1) {
    x.className += " w3-show";
  } else { 
    x.className = x.className.replace(" w3-show", "");
  }
}
</script>

</body>
</html>
