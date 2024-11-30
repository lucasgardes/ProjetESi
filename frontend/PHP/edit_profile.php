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
    if (!empty($_POST['firstname']) && !empty($_POST['lastname']) && !empty($_POST['email'])) {
        $updateProfileSQL = $pdo->prepare("UPDATE client SET firstname = ?, lastname = ?, email = ? WHERE id = ?");
        if ($updateProfileSQL->execute([$_POST['firstname'], $_POST['lastname'], $_POST['email'], $_SESSION['user_id']])) {
            $_SESSION['message'] = "Profil mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du profil.";
        }
    }
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        // Vérification du mot de passe actuel
        $passwordCheckSQL = $pdo->prepare("SELECT password FROM client WHERE id = ?");
        $passwordCheckSQL->execute([$userId]);
        $hashedPassword = $passwordCheckSQL->fetchColumn();
        if (password_verify($_POST['current_password'], $hashedPassword)) {
            // Vérification que les nouveaux mots de passe correspondent
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                // Mise à jour du mot de passe
                $newHashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $updatePasswordSQL = $pdo->prepare("UPDATE client SET password = ? WHERE id = ?");
                if ($updatePasswordSQL->execute([$newHashedPassword, $userId])) {
                    $_SESSION['message'] = "Mot de passe mis à jour avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour du mot de passe.";
                }
            } else {
                $_SESSION['error'] = "Les nouveaux mots de passe ne correspondent pas.";
            }
        } else {
            $_SESSION['error'] = "Le mot de passe actuel est incorrect.";
        }
    }
    header("Location: edit_profile.php");
    exit;
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
        <form method="post">
            <h3>Modifier votre mot de passe</h3>
            <div class="form-group">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                oninput="this.setCustomValidity(this.value !== document.getElementById('new_password').value ? 'Les mots de passe ne correspondent pas.' : '')">
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
