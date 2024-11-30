<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion des Déchets - Green City</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="../CSS/index.css">
    <?php include 'header.php'; ?>
</head>
<body>

<!-- En-tête -->
<header class="w3-container w3-green w3-center" style="padding:128px 16px">
    <h1 class="w3-margin w3-jumbo">Gestion des Déchets - Green City</h1>
    <p class="w3-xlarge">Une collecte des déchets efficace et respectueuse de l'environnement</p>
    <button class="w3-button w3-black w3-padding-large w3-large w3-margin-top">En savoir plus</button>
</header>

<!-- Première Section -->
<div class="w3-row-padding w3-padding-64 w3-container">
    <div class="w3-content">
        <div class="w3-twothird">
            <h1>À Propos de Notre Service</h1>
            <h5 class="w3-padding-32">Découvrez comment nous transformons la collecte des déchets grâce à notre flotte de vélos-cargos électriques, pour rendre notre ville plus propre et plus verte.</h5>
            <p class="w3-text-grey">Notre approche innovante réduit la congestion du trafic et les émissions, offrant une solution durable pour la gestion des déchets. Rejoignez-nous dans notre mission pour une ville plus respectueuse de l'environnement.</p>
        </div>

        <div class="w3-third w3-center">
            <i class="fa fa-recycle w3-padding-64 w3-text-green"></i>
        </div>
    </div>
</div>

<!-- Deuxième Section -->
<div class="w3-row-padding w3-light-grey w3-padding-64 w3-container">
    <div class="w3-content">
        <div class="w3-third w3-center">
            <i class="fa fa-bicycle w3-padding-64 w3-text-green w3-margin-right"></i>
        </div>

        <div class="w3-twothird">
            <h1>Rejoignez Notre Équipe</h1>
            <h5 class="w3-padding-32">Vous souhaitez faire partie de notre équipe de cyclistes ? Découvrez les avantages et comment vous pouvez contribuer à une ville plus verte.</h5>
            <p class="w3-text-grey">Nous sommes toujours à la recherche de personnes motivées partageant notre passion pour la durabilité environnementale. Postulez dès aujourd'hui et aidez-nous à faire la différence !</p>
        </div>
    </div>
</div>

<!-- Section de Message -->
<div class="w3-container w3-black w3-center w3-opacity w3-padding-64">
    <h1 class="w3-margin w3-xlarge">Donner aux communautés les moyens de préserver un environnement plus propre et plus sain</h1>
</div>

<!-- Pied de page -->
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
