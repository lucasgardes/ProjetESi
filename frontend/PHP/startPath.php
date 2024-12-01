<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../../pdo.php';
require 'graph.php';
require_once 'class/bicycle.php';

$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['frontend_user_id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['frontend_user_id'];

// Fetch all bicycles with their current status and assigned paths if any
$stmt = $pdo->query("SELECT b.id, p.id AS path_id, p.start_stop_id, s1.name AS stop1Name, s2.name AS stop2Name, s3.name AS stop3Name, s4.name AS stop4Name
                     FROM bicycles b
                     LEFT JOIN paths p ON p.id = b.path_id
                     LEFT JOIN stops s1 ON s1.id = p.start_stop_id
                     LEFT JOIN stops s2 ON s2.id = p.stop2_id
                     LEFT JOIN stops s3 ON s3.id = p.stop3_id
                     LEFT JOIN stops s4 ON s4.id = p.stop4_id
                     WHERE b.client_id IS NOT NULL");
$bicycles = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['start_trip'])) {
    $stmt = $pdo->prepare("SELECT p.*
                     FROM `paths` p
                     WHERE p.id = ?");
    $stmt->execute([$_POST['path_id']]);
    $path = $stmt->fetch();
    $stmt = $pdo->prepare("SELECT s.name
                     FROM `bicycles` b
                     LEFT JOIN stops s on s.id = b.stop_id
                     WHERE b.id = ?");
    $stmt->execute([$_POST['bicycle_id']]);
    $bicycleInfo = $stmt->fetch();
    $bicycle = new Bicycle($_POST['bicycle_id'], $bicycleInfo['name'], $pdo);
    $bicycle->moveToDestination($path["start_stop_id"], $graph);
    $stops = ['stop4_id', 'stop3_id', 'stop2_id'];
    foreach ($stops as $stop) {
        if (isset($path[$stop]) && !is_null($path[$stop])) {
            $bicycle->moveToDestination($path[$stop], $graph, true, $path);
            break;
        }
    }
    if ($bicycle->currentLoad == 200) {
        $bicycle->returnToBase($graph);
    }
    header("Location: startPath.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Bicycle Trips</title>
</head>
<body>
    <?php include 'header.php';?>
    <div class="container mt-5">
        <h2>Manage Bicycle Trips</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success" role="alert">
                <?= $_SESSION['message'] ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Current Path</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bicycles as $bicycle): ?>
                <tr>
                    <td><?= htmlspecialchars($bicycle['id']) ?></td>
                    <td><?= htmlspecialchars($bicycle['stop1Name']) ?> -> <?= htmlspecialchars($bicycle['stop2Name']) ?> -> <?= htmlspecialchars($bicycle['stop3Name']) ?> -> <?= htmlspecialchars($bicycle['stop4Name']) ?></td>
                    <td>
                        <?php if ($bicycle['path_id']): ?>
                        <form method="post">
                            <input type="hidden" name="bicycle_id" value="<?= $bicycle['id'] ?>">
                            <input type="hidden" name="path_id" value="<?= $bicycle['path_id'] ?>">
                            <button type="submit" name="start_trip" class="btn btn-primary">Start Trip</button>
                        </form>
                        <?php else: ?>
                        No actions available
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
