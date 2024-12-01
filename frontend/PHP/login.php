<?php
    include '../../pdo.php';
    session_start();
    $post = $_POST;
    $logged = false;
    if (isset($post) && !empty($post)) {
        if (isset($post['firstname']) && isset($post['lastname'])) {
            $firstname = $post['firstname'];
            $lastname = $post['lastname'];
        }
        $email = $post['email'];
        $password = $post['password'];

        $stmt = $pdo->prepare("SELECT id, password FROM client WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            // Vérifier le mot de passe avec password_verify()
            if (password_verify($password, $result['password'])) {
                // Mot de passe correct, connexion réussie
                $_SESSION['frontend_user_id'] = $result['id'];
                $_SESSION['email'] = $email;
                $_SESSION['loggedin'] = true;

                $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php';
                if (strpos($redirect_url, 'backend') !== false) {
                    // Si "backend" est présent, rediriger vers l'index
                    unset($_SESSION['redirect_url']); // Supprime la redirection de la session
                    $redirect_url = 'index.php';
                } else {
                    unset($_SESSION['redirect_url']);
                }
                header("Location: " . $redirect_url);
                exit;
            } else {
                // Mot de passe incorrect
                $_SESSION['error'] = "Mot de passe incorrect.";
                header("Location: login.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Ce compte n'existe pas";
            header("Location: login.php");
            exit;
        }
    }
    $successMessage = isset($_SESSION['message']) ? $_SESSION['message'] : '';
    $errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : '';
    unset($_SESSION['message'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion - Green City Waste Collection</title>
        <link rel="stylesheet" href="../CSS/login.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <body>

    <div class="login-container">
        <form action="login.php" method="post" class="login-form">
            <h2>Connexion</h2>
            <?php if ($successMessage) echo '<div class="alert alert-success" role="alert">' . $successMessage . '</div>'; ?>
            <?php if ($errorMessage) echo '<div class="alert alert-danger" role="alert">' . $errorMessage . '</div>'; ?>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        <div class="signup-link">
            <p>Vous n'avez pas encore de compte ? <a href="signup.php">Créer un compte</a></p>
        </div>
    </div>

    </body>
</html>
