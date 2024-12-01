<?php
try {
    require "pdo.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $blockedStations = json_decode($_POST['blockedStations']);
        $deletedStations = json_decode($_POST['deletedStations']);
        $unblockStations = json_decode($_POST['unblockStations']);

        if (empty($blockedStations) && empty($deletedStations) && empty($unblockStations)) {
            echo json_encode(['status' => 'error', 'message' => 'Aucune donnée reçue']);
            exit;
        }

        // requête pour obtenir l'ID des arrêts en fonction du nom depuis table stops
        $stmt = $pdo->prepare("SELECT id FROM stops WHERE name = :name");

        $stmtInsertBlocked = $pdo->prepare("INSERT INTO blocked_stop (stop_id, name, etat) VALUES (?, ?, 'blocked') 
                                            ON DUPLICATE KEY UPDATE etat = 'blocked'");
        foreach ($blockedStations as $station) {
            // obtenir l'ID des arrêts en fonction du nom depuis table stops
            $stmt->execute(['name' => $station]);
            $stop = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stop) {
                $stmtInsertBlocked->execute([$stop['id'], $station]);
            } else {
                //log d'erreru
                error_log("L'arrêt '{$station}' n'existe pas dans la table stops.");
            }
        }

        $stmtInsertDeleted = $pdo->prepare("INSERT INTO blocked_stop (stop_id, name, etat) VALUES (?, ?, 'deleted') 
                                            ON DUPLICATE KEY UPDATE etat = 'deleted'");
        foreach ($deletedStations as $station) {
            // obtenir l'ID des arrêts en fonction du nom depuis table stops
            $stmt->execute(['name' => $station]);
            $stop = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stop) {
                $stmtInsertDeleted->execute([$stop['id'], $station]);
            } else {
                //log d'erreur
                error_log("L'arrêt '{$station}' n'existe pas dans la table stops.");
            }
        }

        foreach ($unblockStations as $station) {
            // obtenir l'ID des arrêts en fonction du nom depuis table stops
            $stmt->execute(['name' => $station]);
            $stop = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stop) {
                $stmtDelete = $pdo->prepare("DELETE FROM blocked_stop WHERE stop_id = ?");
                $stmtDelete->execute([$stop['id']]);
            } else {
                    // log d'erreur
                error_log("L'arrêt '{$station}' n'existe pas dans la table stops.");
            }
        }


        echo json_encode(['status' => 'success', 'message' => 'Les arrêts ont été mis à jour.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Méthode de requête non supportée.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>
