<?php
    include 'pdo.php';
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

        $servername = "mysql-projet-poubelle.alwaysdata.net";
        $username = "348216";
        $password_bdd = "mdp4B2D2Pr0j€t";
        $dbname = "projet-poubelle_bdd";

        $conn = new mysqli($servername, $username, $password_bdd, $dbname);

        if ($conn->connect_error) {
            die("La connexion a échoué : " . $conn->connect_error);
        }
        $stmt = $pdo->prepare("SELECT id FROM client WHERE email = ? AND `password` = ?");
        $stmt->execute([$email, $password]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($result)) {
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['email'] = $email;
            $_SESSION['loggedin'] = true;
            
            $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php';
            unset($_SESSION['redirect_url']);
            header("Location: " . $redirect_url);
            exit;
        }

        $conn->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion - Green City Waste Collection</title>
        <link rel="stylesheet" href="../CSS/login.css">
    </head>
    <body>

    <div class="login-container">
        <form action="login.php" method="post" class="login-form">
            <h2>Connexion</h2>
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
    </div>

    </body>
</html>