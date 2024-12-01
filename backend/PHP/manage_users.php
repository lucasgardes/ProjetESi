<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../../pdo.php';

$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['backend_user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT u.role
                     FROM users u
                     WHERE u.id = ?");
$stmt->execute(params: [$_SESSION['backend_user_id']]);
$user_role = $stmt->fetch();
if ($user_role['role'] != 'admin') {
    if ($user_role['role'] != 'RH') {
        header("Location: index.php");
        exit;
    }
}

$userId = $_SESSION['backend_user_id'];

// Fetch all users and their associated bicycle if any
$stmt = $pdo->query("SELECT c.*, b.id AS bicycle_id
                     FROM client c
                     LEFT JOIN bicycles b ON b.client_id = c.id");
$users = $stmt->fetchAll();

$stmt = $pdo->query("SELECT b.*
                     FROM bicycles b
                     WHERE b.client_id IS NULL");
$bicycles_available = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_user_infos'])) {
        $stmt = $pdo->prepare("UPDATE client SET `admin` = ? WHERE id = ?");
        $stmt->execute([$_POST['is_admin'], $_POST['user_id']]);
        if (isset($_POST['previous_bicycle_id']) && !is_null($_POST['previous_bicycle_id'])) {
            $stmt = $pdo->prepare("UPDATE bicycles SET client_id = NULL WHERE id = ?");
            $stmt->execute([$_POST['previous_bicycle_id']]);
        }
        if (isset($_POST['bicycle_id']) && !is_null($_POST['bicycle_id'])) {
            $stmt = $pdo->prepare("UPDATE bicycles SET client_id = ? WHERE id = ?");
            $stmt->execute([$_POST['user_id'], $_POST['bicycle_id']]);
        }
    }
    header("Location: manage_users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php include 'header.php'; ?>
</head>
<body>
    <div class="container mt-5">
        <h2>Gestion des Utilisateurs</h2>
        <table class="table table-secondary table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Vélo Associé</th>
                    <th>Vélos Disponibles</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                    <form method="post">
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['lastname']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= $user['bicycle_id'] ? $user['bicycle_id'] : 'None' ?></td>
                    <td>
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="previous_bicycle_id" value="<?= $user['bicycle_id'] ?>">
                        <select class="form-select" name="bicycle_id">
                            <option value="">Aucun</option>
                            <?php foreach ($bicycles_available as $bicycle): ?>
                                <option value="<?= $bicycle['id'] ?>"><?= $bicycle['id'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="submit" name="update_user_infos" class="btn btn-primary">Modifier</button>
                    </td>
                </form> 
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
