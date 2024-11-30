<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../../pdo.php';
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'header.php'; ?>
    <title>Analyse et Rapports</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Statistiques de Performance des Vélos</h2>
        <table class="table table-secondary table-striped table-bordered">
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
        <table class="table table-secondary table-striped table-bordered">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>