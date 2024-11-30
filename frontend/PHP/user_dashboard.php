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
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT ph.id
FROM bicycles b
LEFT JOIN path_history ph ON ph.client_id = b.client_id AND ph.path_id = b.path_id
WHERE b.client_id = ?");
$stmt->execute([$userId]);
$assignedPathInfo = $stmt->fetch();

if (!is_null($assignedPathInfo['id'])) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->query("SELECT p.id, s1.name AS stop1Name, s2.name AS stop2Name, s3.name AS stop3Name, s4.name AS stop4Name, p.distance
FROM paths p
LEFT JOIN stops s1 ON s1.id = p.start_stop_id
LEFT JOIN stops s2 ON s2.id = p.stop2_id
LEFT JOIN stops s3 ON s3.id = p.stop3_id
LEFT JOIN stops s4 ON s4.id = p.stop4_id
WHERE p.is_assigned = 0");
$paths = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['start_path'])) {
    $pathId = $_POST['path_id'];
    $stmt = $pdo->prepare("UPDATE paths SET is_assigned = 1 WHERE id = ?");
    $stmt->execute([$pathId]);
    $stmt = $pdo->prepare("INSERT INTO path_history (client_id, path_id) VALUES(?, ?)");
    $stmt->execute([$userId, $pathId]);

    // Message de confirmation
    $_SESSION['message'] = "Trajet assigné avec succès.";
    header("Location: user_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord utilisateur</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="../CSS/user_dashboard.css">
</head>
<body>
    <?php include 'header.php';?>
    <div class="container mt-5">
        <h2>Tableau de bord utilisateur</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>
        <h3>Trajets disponibles</h3>
        <table id="pathsTable" class="table table-secondary table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Itinéraire</th>
                    <th>Distance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paths as $path): ?>
                <tr>
                    <td><?= htmlspecialchars($path['id']) ?></td>
                    <td><?= htmlspecialchars($path['stop1Name']) ?>
                        <?= isset($path['stop2Name']) && $path['stop2Name'] != "" ? " -> " . htmlspecialchars($path['stop2Name']) : "" ?>
                        <?= isset($path['stop3Name']) && $path['stop3Name'] != "" ? " -> " . htmlspecialchars($path['stop3Name']) : "" ?>
                        <?= isset($path['stop4Name']) && $path['stop4Name'] != "" ? " -> " . htmlspecialchars($path['stop4Name']) : "" ?></td>
                    <td><?= htmlspecialchars($path['distance']) ?>m</td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="path_id" value="<?= $path['id'] ?>">
                            <button type="submit" name="start_path" class="btn btn-primary">Démarrer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap5.min.js"></script>
    <script src="../JS/user_dashboard.js"></script>

    <script>
    // Activer DataTables
    $(document).ready(function() {
        $('#pathsTable').DataTable();
    });
    </script>
</body>
</html>
