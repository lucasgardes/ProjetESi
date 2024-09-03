<?php
    $servername = "mysql-projet-poubelle.alwaysdata.net";
    $username = "348216";
    $password = "mdp4B2D2Pr0j€t";
    $dbname = "projet-poubelle_bdd";

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname;port=3306;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Erreur de connexion : " . $e->getMessage();
    }
?>