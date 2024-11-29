<?php
$servername = "mysql-projet-poubelle.alwaysdata.net";
$username = "348216";
$password = "mdp4B2D2Pr0j€t";
$dbname = "projet-poubelle_bdd";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $bikeId = $data['bike_id'];

        if (empty($bikeId)) {
            echo json_encode(['status' => 'error', 'message' => 'ID de vélo manquant']);
            exit;
        }

        // Requête pour `bicycles`
        $stmt = $pdo->prepare("SELECT path_id FROM bicycles WHERE id = :bike_id");
        $stmt->execute(['bike_id' => $bikeId]);
        $bike = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($bike) {
            $pathId = $bike['path_id'];

            // Requête pour `paths`
            $stmt = $pdo->prepare("SELECT start_stop_id, stop2_id, stop3_id, stop4_id
                FROM paths WHERE id = :path_id");
            $stmt->execute(['path_id' => $pathId]);
            $path = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($path) {
                $stopIds = [
                    $path['start_stop_id'],
                    $path['stop2_id'],
                    $path['stop3_id'],
                    $path['stop4_id']
                ];

                $placeholders = implode(',', array_fill(0, count($stopIds), '?'));
                $stmt = $pdo->prepare("SELECT id, name FROM stops WHERE id IN ($placeholders) ORDER BY FIELD(id, " . implode(',', $stopIds) . ")");
                $stmt->execute($stopIds);
                $stops = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Renvoie les arrêts sous forme de JSON
                echo json_encode(['status' => 'success', 'stops' => $stops]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Itinéraire non trouvé']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Vélo non trouvé']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Méthode de requête non supportée']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>
