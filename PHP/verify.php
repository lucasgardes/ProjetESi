<?php
$host = 'localhost';
$db = 'nom_de_votre_base_de_donnees';
$user = 'utilisateur_de_la_base_de_donnees';
$pass = 'mot_de_passe_de_la_base_de_donnees';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    $sql = "UPDATE users SET verified = 1 WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    echo "Votre compte a été vérifié avec succès!";
}
?>
