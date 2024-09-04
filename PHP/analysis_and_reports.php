<?php
session_start();
require 'pdo.php';
$_SESSION['user_id'] = 1;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("SELECT b.id, COUNT(ph.id) AS total_trips, b.traveled_distance AS total_distance, AVG(p.distance) AS average_distance, SUM(ph.load_collected) AS load_collected
                     FROM bicycles b
                     LEFT JOIN path_history ph ON ph.client_id = b.client_id
                     LEFT JOIN paths p ON ph.path_id = p.id
                     GROUP BY b.id");
$bicycleStats = $stmt->fetchAll();

$stmt = $pdo->query("SELECT p.id, p.start_stop_id, p.stop2_id, p.stop3_id, p.stop4_id, COUNT(ph.id) AS usage_count
                     FROM paths p
                     LEFT JOIN path_history ph ON ph.path_id = p.id
                     GROUP BY p.id
                     ORDER BY usage_count DESC
                     LIMIT 10");
$frequentPaths = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse et Rapports</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Statistiques de Performance des Vélos</h2>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>ID du Vélo</th>
                    <th>Total des Trajets</th>
                    <th>Distance Totale (m)</th>
                    <th>Distance Moyenne par Trajet (m)</th>
                    <th>Déchets collectés</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bicycleStats as $stat): ?>
                <tr>
                    <td><?= htmlspecialchars($stat['id']) ?></td>
                    <td><?= htmlspecialchars($stat['total_trips']) ?></td>
                    <td><?= htmlspecialchars($stat['total_distance']) ?></td>
                    <td><?= htmlspecialchars($stat['average_distance']) ?></td>
                    <td><?= htmlspecialchars($stat['load_collected']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Itinéraires les Plus Fréquentés</h2>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID de l'Itinéraire</th>
                    <th>Comptage d'Utilisation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($frequentPaths as $path): ?>
                <tr>
                    <td><?= htmlspecialchars($path['id']) ?></td>
                    <td><?= htmlspecialchars($path['usage_count']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>