<?php
require 'pdo.php'; // Assurez-vous que ce fichier contient la connexion à la base de données

class Bicycle {
    // Utilisez la définition de la classe Bicycle précédemment fournie ici
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $stmt = $pdo->prepare("SELECT `admin` FROM client WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result['admin']) {
        header("Location: login.php");
        exit;
    }
  $logged = true;
} else {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM stops");
$stmt->execute();
$stops = $stmt->fetchAll(PDO::FETCH_ASSOC);

function fetchAllBicycles($pdo) {
    $stmt = $pdo->query("SELECT * FROM bicycles");
    return $stmt->fetchAll(PDO::FETCH_CLASS, 'Bicycle');
}

$bicycles = fetchAllBicycles($pdo);

foreach ($bicycles as $bicycle) {
    $stmt = $pdo->prepare("SELECT name FROM stops WHERE id = ?");
    $stmt->execute([$bicycle->stop_id]);
    $currentStopName[$bicycle->id] = $stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gérer la logique de mise à jour ici
    if (isset($_POST['update'])) {
        $stmt = $pdo->prepare("UPDATE bicycles SET stop_id = ?, autonomy = ?, `load` = ? WHERE id = ?");
        $stmt->execute([$_POST['position'], $_POST['autonomy'], $_POST['load'], $_POST['id']]);
        header("Location: manage_bicycles.php");
    } elseif (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO bicycles (stop_id, autonomy, `load`) VALUES (121, 50, 0)");
        $stmt->execute();
        header("Location: manage_bicycles.php");
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM bicycles WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: manage_bicycles.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Vélos</title>
    <!-- Intégration de Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../CSS/manage_bicycles.css">
</head>
<body>
    <?php include 'header.php';?>
    <div class="container pt-5">
        <h1 class="text-center text-success title">Gestion des Vélos</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Position</th>
                        <th>Autonomie</th>
                        <th>Charge Actuelle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bicycles as $bicycle): ?>
                        <tr>
                        <form method="post">
                            <td><?= $bicycle->id ?></td>
                            <td>
                                <select class="form-control form-control-select" name="position">
                                    <?php foreach ($stops as $stop): ?>
                                        <option value="<?= $stop['id'] ?>" <?= $currentStopName[$bicycle->id] == $stop['name'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($stop['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" class="form-control" name="autonomy" value="<?= $bicycle->autonomy ?>"></td>
                            <td><input type="number" class="form-control" name="load" value="<?= $bicycle->load ?>"></td>
                            <td>
                                <input type="hidden" name="id" value="<?= $bicycle->id ?>">
                                <button type="submit" class="btn btn-success btn-sm" name="update">Mettre à jour</button>
                                <button type="submit" class="btn btn-danger btn-sm" name="delete">Supprimer</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="post">
                <button type="submit" class="btn btn-primary btn-block" name="add">Ajouter un nouveau Vélo</button>
            </form>
        </div>
    </div>

    <!-- Intégration de jQuery et Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../JS/manage_bicycles.js"></script>
</body>
</html>