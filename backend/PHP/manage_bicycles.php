<?php
require '../../pdo.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $logged = true;
} else {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT u.role
                     FROM users u
                     WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_role = $stmt->fetch();
if ($user_role['role'] != 'admin') {
    if ($user_role['role'] != 'réseau') {
        header("Location: index.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM stops");
$stmt->execute();
$stops = $stmt->fetchAll(PDO::FETCH_ASSOC);

function fetchAllBicycles($pdo) {
    $stmt = $pdo->query("SELECT * FROM bicycles");
    return $stmt->fetchAll();
}

$bicycles = fetchAllBicycles($pdo);

foreach ($bicycles as $bicycle) {
    $stmt = $pdo->prepare("SELECT name FROM stops WHERE id = ?");
    $stmt->execute([$bicycle['stop_id']]);
    $currentStopName[$bicycle['id']] = $stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../CSS/manage_bicycles.css">
</head>
<body>
    <div class="container pt-5">
        <h1 class="text-center text-success title">Gestion des Vélos</h1>
        <div class="table-responsive">
            <table class="table table-secondary table-striped table-bordered table-hover">
                <thead class="table-dark">
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
                            <td><?= $bicycle['id'] ?></td>
                            <td>
                                <select class="form-select" name="position">
                                    <?php foreach ($stops as $stop): ?>
                                        <option value="<?= $stop['id'] ?>" <?= $currentStopName[$bicycle['id']] == $stop['name'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($stop['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" class="form-control" name="autonomy" value="<?= $bicycle['autonomy'] ?>"></td>
                            <td><input type="number" class="form-control" name="load" value="<?= $bicycle['load'] ?>"></td>
                            <td>
                                <input type="hidden" name="id" value="<?= $bicycle['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm" name="update">Mettre à jour</button>
                                <button type="submit" class="btn btn-danger btn-sm" name="delete">Supprimer</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="post">
                <button type="submit" class="btn btn-primary w-100" name="add">Ajouter un nouveau Vélo</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../JS/manage_bicycles.js"></script>
</body>
</html>
