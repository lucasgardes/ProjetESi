<?php
$servername = "mysql-projet-poubelle.alwaysdata.net";
$username = "348216";
$password = "mdp4B2D2Pr0j€t";
$dbname = "projet-poubelle_bdd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$arret1 = $_POST['arret1'];
$arret2 = $_POST['arret2'];
$statut = "blocked";

$stmt = $conn->prepare("INSERT INTO rue_block (arret_1, arret_2, statut) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $arret1, $arret2, $statut);

if ($stmt->execute()) {
    echo "Rue bloquée ajoutée avec succès";
} else {
    echo "Erreur : " . $stmt->error;
}

$stmt->close();
$conn->close();
