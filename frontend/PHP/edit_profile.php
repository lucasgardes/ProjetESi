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

$profileInfoSQL = $pdo->prepare("SELECT firstname, lastname, email FROM client WHERE id = ?");
$profileInfoSQL->execute([$userId]);
$user = $profileInfoSQL->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $updateProfileSQL = $pdo->prepare("UPDATE client SET firstname = ?, lastname = ?, email = ? WHERE id = ?");
    if ($updateProfileSQL->execute([$_POST['firstname'], $_POST['lastname'], $_POST['email'], $_SESSION['user_id']])) {
        $_SESSION['message'] = "Profil mis à jour avec succès.";
        header("Location: edit_profile.php");
        exit;
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour du profil.";
    }
}

$successMessage = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification du Profil</title>
    <link rel="stylesheet" href="../CSS/edit_profile.css">
</head>
<body>
    <?php include 'header.php';?>
    <div class="container mt-5">
        <h2>Modifier votre profil</h2>
        <?php if ($successMessage) echo '<div class="alert alert-success" role="alert">' . $successMessage . '</div>'; ?>
        <?php if ($errorMessage) echo '<div class="alert alert-danger" role="alert">' . $errorMessage . '</div>'; ?>
        <form method="post">
            <div class="form-group">
                <label for="firstname">Prénom</label>
                <input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
            </div>
            <div class="form-group">
                <label for="lastname">Nom</label>
                <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mx-auto pt-4">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>

    <!-- Inclusion de Bootstrap JS et ses dépendances -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
</body>
</html>
