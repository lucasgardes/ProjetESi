<?php
$servername = "localhost";
$username = "348216";
$password_bdd = "mdp4B2D2Pr0j€t";
$dbname = "mysql-projet-poubelle";
$conn = new mysqli($servername, $username, $password_bdd, $dbname);

if (!$conn->set_charset("utf8")) {
    echo "Erreur lors du chargement du jeu de caractères utf8 : " . $conn->error;
} else {
    echo "Jeu de caractères actuel : " . $conn->character_set_name();
}

if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    $sql = "UPDATE client SET verified = 1 WHERE email = " . $email;
    $result = $conn->query($sql);

    header('Location: index.php');
}
?>
