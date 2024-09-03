<?php
include 'pdo.php';

$_SESSION['id'] = 1;
if (!isset($_SESSION['id'])) {
    echo "Vous n'êtes pas autorisé à accéder à cette page.";
    exit;
}

// Mise à jour des informations de l'utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE client SET firstname = ?, lastname = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$_POST['firstname'], $_POST['lastname'], $_POST['email'], $_SESSION['id']])) {
        echo "Profil mis à jour avec succès.";
    } else {
        echo "Erreur lors de la mise à jour du profil.";
    }
}
?>
