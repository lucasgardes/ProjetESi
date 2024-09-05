<?php
include 'pdo.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Mise à jour des informations de l'utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE client SET firstname = ?, lastname = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$_POST['firstname'], $_POST['lastname'], $_POST['email'], $_SESSION['user_id']])) {
        echo "Profil mis à jour avec succès.";
    } else {
        echo "Erreur lors de la mise à jour du profil.";
    }
}
?>
