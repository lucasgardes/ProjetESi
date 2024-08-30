<?php
require 'pdo.php'; // Assurez-vous que ce fichier contient la connexion à la base de données

class Bicycle {
    // Utilisez la définition de la classe Bicycle précédemment fournie ici
}

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
        $stmt = $pdo->prepare("SELECT id FROM stops WHERE name = ?");
        $stmt->execute([$_POST['position']]);
        $stop_id = $stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE bicycles SET stop_id = ?, autonomy = ?, `load` = ? WHERE id = ?");
        $stmt->execute([$stop_id, $_POST['autonomy'], $_POST['load'], $_POST['id']]);
    } elseif (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO bicycles (stop_id, autonomy, `load`) VALUES (121, 50, 0)");
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM bicycles WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Vélos</title>
</head>
<body>
    <h1>Gestion des Vélos</h1>
    <table border="1">
        <thead>
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
                    <td><input type="text" name="position" value="<?= $currentStopName[$bicycle->id] ?>"></td>
                    <td><input type="number" name="autonomy" value="<?= $bicycle->autonomy ?>"></td>
                    <td><input type="number" name="load" value="<?= $bicycle->load ?>"></td>
                    <td>
                        <input type="hidden" name="id" value="<?= $bicycle->id ?>">
                        <button type="submit" name="update">Mettre à jour</button>
                        <button type="submit" name="delete">Supprimer</button>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
            <tr>
                <form method="post">
                    <td>Nouveau</td>
                    <td><button type="submit" name="add">Ajouter Vélo</button></td>
                </form>
            </tr>
        </tbody>
    </table>
</body>
</html>
