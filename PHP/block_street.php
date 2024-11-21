<?php
$servername = "mysql-projet-poubelle.alwaysdata.net";
$username = "348216";
$password = "mdp4B2D2Pr0j€t";
$dbname = "projet-poubelle_bdd";

try {
    // Créer la connexion
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Afficher ou loguer les données reçues
        $blockedStations = json_decode($_POST['blockedStations']);
        $deletedStations = json_decode($_POST['deletedStations']);

        // Débogage : vérifier ce qui est reçu
        error_log("Blocked Stations: " . json_encode($blockedStations));
        error_log("Deleted Stations: " . json_encode($deletedStations));

        // Vérifier si les données sont bien reçues
        if (empty($blockedStations) && empty($deletedStations)) {
            echo json_encode(['status' => 'error', 'message' => 'Aucune donnée reçue']);
            exit;
        }

        // Insérer les arrêts bloqués
        $stmt = $pdo->prepare("INSERT INTO blocked_stop (name, etat) VALUES (?, 'blocked') ON DUPLICATE KEY UPDATE etat = 'blocked'");
        foreach ($blockedStations as $station) {
            $stmt->execute([$station]);
        }

        // Insérer les arrêts supprimés
        $stmt = $pdo->prepare("INSERT INTO blocked_stop (name, etat) VALUES (?, 'deleted') ON DUPLICATE KEY UPDATE etat = 'deleted'");
        foreach ($deletedStations as $station) {
            $stmt->execute([$station]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Les arrêts ont été mis à jour.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Méthode de requête non supportée.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>