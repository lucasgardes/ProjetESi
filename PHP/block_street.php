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
        // Récupérer les arrêts bloqués et supprimés envoyés par POST
        $blockedStations = json_decode($_POST['blockedStations']);
        $deletedStations = json_decode($_POST['deletedStations']);

        // Vérifier si les données sont bien reçues
        if (empty($blockedStations) && empty($deletedStations)) {
            echo json_encode(['status' => 'error', 'message' => 'Aucune donnée reçue']);
            exit;
        }

        // Préparer la requête pour obtenir l'ID des arrêts en fonction du nom
        $stmt = $pdo->prepare("SELECT id FROM stops WHERE name = :name");

        // Insérer les arrêts bloqués avec stop_id
        $stmtInsertBlocked = $pdo->prepare("INSERT INTO blocked_stop (stop_id, name, etat) VALUES (?, ?, 'blocked') 
                                            ON DUPLICATE KEY UPDATE etat = 'blocked'");

        foreach ($blockedStations as $station) {
            // Récupérer l'ID du stop basé sur le nom de l'arrêt
            $stmt->execute(['name' => $station]);
            $stop = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stop) {
                // Insérer ou mettre à jour l'enregistrement dans blocked_stop avec stop_id
                $stmtInsertBlocked->execute([$stop['id'], $station]);
            } else {
                // Gérer le cas où l'arrêt n'est pas trouvé dans la table stops
                error_log("L'arrêt '{$station}' n'existe pas dans la table stops.");
            }
        }

        // Insérer les arrêts supprimés avec stop_id
        $stmtInsertDeleted = $pdo->prepare("INSERT INTO blocked_stop (stop_id, name, etat) VALUES (?, ?, 'deleted') 
                                            ON DUPLICATE KEY UPDATE etat = 'deleted'");

        foreach ($deletedStations as $station) {
            // Récupérer l'ID du stop basé sur le nom de l'arrêt
            $stmt->execute(['name' => $station]);
            $stop = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stop) {
                // Insérer ou mettre à jour l'enregistrement dans blocked_stop avec stop_id
                $stmtInsertDeleted->execute([$stop['id'], $station]);
            } else {
                // Gérer le cas où l'arrêt n'est pas trouvé dans la table stops
                error_log("L'arrêt '{$station}' n'existe pas dans la table stops.");
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Les arrêts ont été mis à jour.']);
    } else {
        // Si la méthode n'est pas POST, envoyer un message d'erreur
        echo json_encode(['status' => 'error', 'message' => 'Méthode de requête non supportée.']);
    }
} catch (PDOException $e) {
    // En cas d'erreur, envoyer un message d'erreur JSON
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>
